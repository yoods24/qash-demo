<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDailyAttendanceTable extends Component implements HasTable, HasSchemas, HasActions
{
    use InteractsWithTable;
    use InteractsWithSchemas;
    use InteractsWithActions;

    public string $selectedDate;

    public string|int|null $tenantParam = null;

    public function boot(): void
    {
        if ($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function updatedSelectedDate(): void
    {
        if (blank($this->selectedDate)) {
            $this->selectedDate = now()->toDateString();
        }

        $this->resetTable();
    }

    #[On('attendance-record-updated')]
    public function refreshFromEvent(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $displayTimezone = $this->displayTimezone();
        $date = $this->resolvedDate()->toDateString();

        return $table
            ->query(
                Attendance::query()
                    ->with(['user:id,first_name,last_name,email'])
                    ->whereDate('work_date', $date)
                    ->orderByDesc('clock_in_at')
            )
            ->columns([
                TextColumn::make('staff_name')
                    ->label('Staff')
                    ->getStateUsing(function (Attendance $record): string {
                        $first = $record->user?->first_name ?? '';
                        $last = $record->user?->last_name ?? '';
                        $full = trim($first . ' ' . $last);

                        return $full !== '' ? $full : ($record->user?->email ?? '-');
                    })
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('user', function (Builder $userQuery) use ($search) {
                            $userQuery->where(function (Builder $nameQuery) use ($search) {
                                $nameQuery->where('first_name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%');
                            });
                        });
                    }),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'present',
                        'danger' => fn ($state) => $state === 'absent',
                        'warning' => fn ($state) => $state === 'half_day',
                        'primary' => fn ($state) => $state === 'holiday',
                        'gray' => fn ($state) => $state === 'leave',
                    ])
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', (string) $state)),

                TextColumn::make('clock_in_at')
                    ->label('Clock In')
                    ->formatStateUsing(function ($state) use ($displayTimezone) {
                        if (!$state) {
                            return '-';
                        }
                        $dt = $state instanceof Carbon ? $state : Carbon::parse($state);
                        return $dt->copy()->setTimezone($displayTimezone)->format('H:i');
                    })
                    ->sortable(),

                TextColumn::make('clock_out_at')
                    ->label('Clock Out')
                    ->formatStateUsing(function ($state) use ($displayTimezone) {
                        if (!$state) {
                            return '-';
                        }
                        $dt = $state instanceof Carbon ? $state : Carbon::parse($state);
                        return $dt->copy()->setTimezone($displayTimezone)->format('H:i');
                    })
                    ->sortable(),

                TextColumn::make('production_seconds')
                    ->label('Production')
                    ->formatStateUsing(fn ($seconds) => $this->formatDuration($seconds))
                    ->toggleable(),

                TextColumn::make('break_seconds')
                    ->label('Break')
                    ->formatStateUsing(fn ($seconds) => $this->formatDuration($seconds))
                    ->toggleable(),

                TextColumn::make('overtime_seconds')
                    ->label('Overtime')
                    ->formatStateUsing(function ($seconds) {
                        $seconds = is_numeric($seconds) ? (int) $seconds : 0;
                        $sign = $seconds < 0 ? '-' : '';
                        return $sign . $this->formatDuration(abs($seconds));
                    })
                    ->toggleable(),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? str_replace('_', ' ', (string) $state) : '-')
                    ->toggleable(),

                TextColumn::make('is_late')
                    ->label('Late?')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->badge()
                    ->colors([
                        'danger' => fn ($state) => (bool) $state,
                        'success' => fn ($state) => !$state,
                    ])
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'half_day' => 'Half Day',
                        'holiday' => 'Holiday',
                        'leave' => 'Leave',
                    ]),
                SelectFilter::make('method')
                    ->label('Method')
                    ->options([
                        'manual' => 'Manual',
                        'geo' => 'Geolocation',
                        'face' => 'Face',
                        'default' => 'Default',
                        'default_combined' => 'Default + Face',
                    ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public function getSelectedDateLabelProperty(): string
    {
        return $this->resolvedDate()->format('l, d M Y');
    }

    protected function resolvedDate(): Carbon
    {
        try {
            return Carbon::parse($this->selectedDate)->startOfDay();
        } catch (\Throwable $e) {
            return now()->startOfDay();
        }
    }

    private function formatDuration($seconds): string
    {
        $seconds = is_numeric($seconds) ? max(0, (int) $seconds) : 0;
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%02dh %02dm', $hours, $minutes);
    }

    private function displayTimezone(): string
    {
        $tenantTimezone = null;
        if (function_exists('tenant') && tenant()) {
            $data = tenant()->data ?? [];
            $tenantTimezone = is_array($data) ? ($data['timezone'] ?? null) : null;
            if (is_string($tenantTimezone) && $tenantTimezone !== '') {
                return $tenantTimezone;
            }
        }

        $configured = config('app.display_timezone') ?? config('app.timezone', 'UTC');
        return is_string($configured) && $configured !== '' ? $configured : 'UTC';
    }

    public function render()
    {
        return view('livewire.backoffice.tables.admin-daily-attendance-table');
    }
}
