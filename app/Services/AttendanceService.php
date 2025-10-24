<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function clockIn(User $user, ?Carbon $at = null, array $options = []): Attendance
    {
        $at = $at ?: now();
        $workDate = $at->toDateString();

        return DB::transaction(function () use ($user, $at, $workDate, $options) {
            $attendance = Attendance::firstOrCreate(
                [
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'work_date' => $workDate,
                ],
                [
                    'shift_id' => $user->shift_id,
                    'status' => 'present',
                ]
            );

            if (!$attendance->clock_in_at) {
                $attendance->clock_in_at = $at;
                $attendance->method = $options['method'] ?? $attendance->method;
                $attendance->clock_in_lat = $options['lat'] ?? null;
                $attendance->clock_in_lng = $options['lng'] ?? null;
                $attendance->clock_in_device = $options['device'] ?? null;
                // compute late based on shift start_time if available
                $attendance->is_late = $this->isLate($attendance, $at);
                $attendance->save();
            }

            return $attendance->fresh();
        });
    }

    public function startBreak(User $user, ?Carbon $at = null, string $type = 'break'): AttendanceBreak
    {
        $at = $at ?: now();
        $attendance = $this->getTodayAttendanceOrFail($user);

        return DB::transaction(function () use ($attendance, $at, $type) {
            // If there is a running break, do nothing
            $running = $attendance->breaks()->whereNull('ended_at')->latest('started_at')->first();
            if ($running) {
                return $running;
            }
            return $attendance->breaks()->create([
                'tenant_id' => $attendance->tenant_id,
                'type' => $type,
                'started_at' => $at,
            ]);
        });
    }

    public function endBreak(User $user, ?Carbon $at = null): ?AttendanceBreak
    {
        $at = $at ?: now();
        $attendance = $this->getTodayAttendanceOrFail($user);

        return DB::transaction(function () use ($attendance, $at) {
            /** @var AttendanceBreak|null $break */
            $break = $attendance->breaks()->whereNull('ended_at')->latest('started_at')->first();
            if (!$break) {
                return null;
            }
            $break->ended_at = $at;
            $break->duration_seconds = max(0, $break->ended_at->diffInSeconds($break->started_at));
            $break->save();

            // Recalculate aggregate break seconds
            $totalBreak = (int) $attendance->breaks()->sum('duration_seconds');
            $attendance->break_seconds = $totalBreak;
            $attendance->save();
            return $break;
        });
    }

    public function clockOut(User $user, ?Carbon $at = null, array $options = []): Attendance
    {
        $at = $at ?: now();
        $attendance = $this->getTodayAttendanceOrFail($user);

        return DB::transaction(function () use ($attendance, $user, $at, $options) {
            if (!$attendance->clock_in_at) {
                // Auto clock-in if somehow missing
                $attendance->clock_in_at = $at;
            }

            $attendance->clock_out_at = $at;
            $attendance->clock_out_lat = $options['lat'] ?? null;
            $attendance->clock_out_lng = $options['lng'] ?? null;
            $attendance->clock_out_device = $options['device'] ?? null;

            // Ensure running break is closed
            $running = $attendance->breaks()->whereNull('ended_at')->latest('started_at')->first();
            if ($running) {
                $running->ended_at = $at;
                $running->duration_seconds = max(0, $running->ended_at->diffInSeconds($running->started_at));
                $running->save();
            }

            // Recalculate aggregates
            $attendance->break_seconds = (int) $attendance->breaks()->sum('duration_seconds');
            $gross = max(0, $attendance->clock_out_at->diffInSeconds($attendance->clock_in_at));
            $attendance->production_seconds = max(0, $gross - $attendance->break_seconds);
            $attendance->overtime_seconds = $this->calculateOvertime($attendance);

            $attendance->save();

            return $attendance->fresh();
        });
    }

    protected function getTodayAttendanceOrFail(User $user): Attendance
    {
        $today = now()->toDateString();
        $attendance = Attendance::firstOrCreate(
            [
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'shift_id' => $user->shift_id,
                'status' => 'present',
            ]
        );
        return $attendance;
    }

    protected function isLate(Attendance $attendance, Carbon $clockIn): bool
    {
        if (!$attendance->shift_id) {
            return false;
        }
        $shift = Shift::find($attendance->shift_id);
        if (!$shift) {
            return false;
        }
        // Build today's scheduled start time without string concatenation
        $date = $attendance->work_date instanceof Carbon
            ? $attendance->work_date->copy()->startOfDay()
            : Carbon::parse($attendance->work_date)->startOfDay();
        $start = $date->copy()->setTimeFromTimeString((string) $shift->start_time);
        return $clockIn->greaterThan($start);
    }

    protected function calculateOvertime(Attendance $attendance): int
    {
        if (!$attendance->shift_id || !$attendance->clock_in_at || !$attendance->clock_out_at) {
            return 0;
        }
        $shift = Shift::find($attendance->shift_id);
        if (!$shift) {
            return 0;
        }
        // Build start/end from date object safely (supports overnight)
        $date = $attendance->work_date instanceof Carbon
            ? $attendance->work_date->copy()->startOfDay()
            : Carbon::parse($attendance->work_date)->startOfDay();
        $start = $date->copy()->setTimeFromTimeString((string) $shift->start_time);
        $end = $date->copy()->setTimeFromTimeString((string) $shift->end_time);
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $scheduledSeconds = $end->diffInSeconds($start);
        return (int) ($attendance->production_seconds - $scheduledSeconds);
    }
}
