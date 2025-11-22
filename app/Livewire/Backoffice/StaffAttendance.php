<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Services\AttendanceService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class StaffAttendance extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithSchemas;
    public string $range = 'month'; // month|7|30
    public bool $onBreak = false;
    public ?float $lat = null;
    public ?float $lng = null;
    public ?bool $geoOk = null;
    public ?int $geoDistance = null; // meters
    public string|int|null $tenantParam = null;

    public function boot(): void
    {
        if ($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function mount(): void
    {
        $this->onBreak = $this->hasRunningBreak();
    }

    protected function service(): AttendanceService
    {
        return app(AttendanceService::class);
    }

    public function clockIn(): void
    {
        $user = Auth::user();
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $user->tenant_id]);

        if ($user->attendance_method === 'default') {
            $effective = ($settings->apply_face_to === 'all' && $settings->default_combined)
                ? 'default_combined'
                : ($settings->default_method ?? 'geo');
        } else {
            $effective = $user->attendance_method; // manual|geo|face
        }

        if ($effective === 'manual') {
            session()->flash('error', 'Your attendance is set to manual by admin.');
            return;
        }

        $needsGeo = in_array($effective, ['geo', 'default_combined'], true);
        if ($needsGeo) {
            $geo = $settings->geofence ?? [];
            if (empty($geo['lat']) || empty($geo['lng']) || empty($geo['radius'])) {
                $this->redirectRoute('backoffice.settings.index', navigate: true);
                return;
            }
        }

        // Validate geofence position before proceeding to face screen (for combined mode)
        if ($needsGeo) {
            if ($this->lat === null || $this->lng === null) {
                session()->flash('error', 'Please allow location access to clock in.');
                return;
            }
            $this->validateGeofence();
            if ($this->geoOk === false) {
                session()->flash('error', 'You are outside the allowed geofence.');
                return;
            }
        }

        $needsFace = ($effective === 'face') || ($effective === 'default_combined');
        if ($needsFace && !$settings->face_recognition_enabled) {
            session()->flash('error', 'Face recognition is disabled in settings.');
            return;
        }
        if ($needsFace) {
            $this->redirectRoute('backoffice.face.attendance', ['tenant' => $this->tenantParam, 'user' => request()->user()], navigate: true);
            return;
        }

        $this->service()->clockIn($user, now(), [
            'method' => $effective,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'device' => request()->userAgent(),
        ]);
    }

    public function clockOut(): void
    {
        $user = Auth::user();
        $this->service()->clockOut($user, now(), [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'device' => request()->userAgent(),
        ]);
        $this->onBreak = false;
    }

    public function toggleBreak(): void
    {
        $user = Auth::user();
        if ($this->onBreak) {
            $this->service()->endBreak($user, now());
            $this->onBreak = false;
        } else {
            $this->service()->startBreak($user, now());
            $this->onBreak = true;
        }
    }

    #[Computed]
    public function overview(): array
    {
        $user = Auth::user();
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $q = Attendance::query()->where('user_id', $user->id)->whereBetween('work_date', [$start, $end]);
        return [
            'totalWorkingDays' => now()->daysInMonth,
            'absentDays' => (clone $q)->where('status', 'absent')->count(),
            'presentDays' => (clone $q)->where('status', 'present')->count(),
            'halfDays' => (clone $q)->where('status', 'half_day')->count(),
            'lateDays' => (clone $q)->where('is_late', true)->count(),
            'holidays' => (clone $q)->where('status', 'holiday')->count(),
        ];
    }

    #[Computed]
    public function records()
    {
        $user = Auth::user();
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();
        return Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date', 'asc')
            ->get();
    }

    #[Computed]
    public function today(): ? Attendance
    {
        $user = Auth::user();
        return Attendance::query()
            ->where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();
    }

    #[Computed]
    public function runningBreakStart(): ?string
    {
        $a = $this->today;
        if (!$a) return null;
        $b = $a->breaks()->whereNull('ended_at')->latest('started_at')->first();
        return $b?->started_at?->toIso8601String();
    }

    public function hasRunningBreak(): bool
    {
        $user = Auth::user();
        $today = now()->toDateString();
        /** @var Attendance|null $a */
        $a = Attendance::query()
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        if (!$a) return false;
        return $a->breaks()->whereNull('ended_at')->exists();
    }

    #[Computed]
    public function requiresGeo(): bool
    {
        $user = Auth::user();
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $user->tenant_id]);
        $effective = $user->attendance_method === 'default'
            ? (($settings->apply_face_to === 'all' && $settings->default_combined) ? 'default_combined' : ($settings->default_method ?? 'geo'))
            : $user->attendance_method;
        return in_array($effective, ['geo', 'default_combined'], true);
    }

    #[Computed]
    public function geofenceConfigured(): bool
    {
        $s = AttendanceSetting::first();
        $g = $s?->geofence ?? [];
        return !empty($g['lat']) && !empty($g['lng']) && !empty($g['radius']);
    }

    #[Computed]
    public function requiresFace(): bool
    {
        $user = Auth::user();
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $user->tenant_id]);
        $effective = $user->attendance_method === 'default'
            ? (($settings->apply_face_to === 'all' && $settings->default_combined) ? 'default_combined' : ($settings->default_method ?? 'geo'))
            : $user->attendance_method;
        return in_array($effective, ['face', 'default_combined'], true);
    }

    #[Computed]
    public function hasFaceDataset(): ?bool
    {
        // Only check if face is required; otherwise it's irrelevant
        if (!($this->requiresFace ?? false)) {
            return null;
        }
        $user = Auth::user();
        $tenantId = function_exists('tenant') ? tenant('id') : null;
        $tenantId = $tenantId ?: (request()->route('tenant'));
        if (!$tenantId) return null;

        try {
            $resp = Http::asForm()
                ->withHeaders(['X-Tenant-Id' => $tenantId])
                ->post('http://127.0.0.1:5001/has_face', [
                    'tenant_id' => $tenantId,
                    'name' => trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                ]);
            if (!$resp->ok()) return null;
            $j = $resp->json();
            if (is_array($j) && array_key_exists('registered', $j)) {
                return (bool) $j['registered'];
            }
            if (is_array($j) && array_key_exists('status', $j)) {
                return $j['status'] === 'ok';
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }


    protected function fmt(int|float|null $seconds): string
    {
        $seconds = (int) ($seconds ?? 0);
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        return sprintf('%02dh %02dm', $h, $m);
    }

    public function validateGeofence(): void
    {
        $user = Auth::user();
        $settings = AttendanceSetting::firstOrCreate(['tenant_id' => $user->tenant_id]);
        $geo = $settings->geofence ?? null;
        if (!$geo || $this->lat === null || $this->lng === null) {
            $this->geoOk = null;
            $this->geoDistance = null;
            return;
        }
        $dist = $this->distanceMeters($this->lat, $this->lng, (float) $geo['lat'], (float) $geo['lng']);
        $this->geoDistance = $dist;
        $this->geoOk = $dist <= (int) ($geo['radius'] ?? 0);
    }

    protected function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earth = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return (int) round($earth * $c);
    }

    public function render()
    {
        return view('livewire.backoffice.staff-attendance', [
            'overview' => $this->overview,
            'today' => $this->today,
            'runningBreakStart' => $this->runningBreakStart,
            'geofence' => optional(AttendanceSetting::first())?->geofence, // will be tenant-scoped via model trait
            'fmt' => fn ($s) => $this->fmt($s),
            'requiresGeo' => $this->requiresGeo,
            'geofenceConfigured' => $this->geofenceConfigured,
            'requiresFace' => $this->requiresFace,
        ]);
    }

}
