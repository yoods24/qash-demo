<?php

namespace App\Livewire\Backoffice\Tables;

use Livewire\Component;
use App\Models\Attendance;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class StaffAttendanceTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public string|int|null $tenantParam = null;

        public function boot(): void
    {
        if ($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function table(Table $table): Table 
    {
        $user = Auth::user();
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        return $table
            ->query(
                Attendance::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('work_date', [$start, $end])
                    ->orderByDesc('work_date')
            )
            ->columns([
                TextColumn::make('work_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

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
                    ->formatStateUsing(fn($state) => str_replace('_',' ', (string) $state)),

                TextColumn::make('clock_in_at')
                    ->label('Clock In')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $dt = $state instanceof \Carbon\Carbon ? $state : \Carbon\Carbon::parse($state);
                        return $dt->copy()->setTimezone(config('app.timezone', 'UTC'))->format('h:i A');
                    })
                    ->toggleable(),

                TextColumn::make('clock_out_at')
                    ->label('Clock Out')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '-';
                        $dt = $state instanceof \Carbon\Carbon ? $state : \Carbon\Carbon::parse($state);
                        return $dt->copy()->setTimezone(config('app.timezone', 'UTC'))->format('h:i A');
                    })
                    ->toggleable(),

                TextColumn::make('production_seconds')
                    ->label('Production')
                    ->formatStateUsing(fn($s) => $this->fmt(is_numeric($s) ? (int)$s : 0)),

                TextColumn::make('break_seconds')
                    ->label('Break')
                    ->formatStateUsing(fn($s) => $this->fmt(is_numeric($s) ? (int)$s : 0)),

                TextColumn::make('overtime_seconds')
                    ->label('Overtime')
                    ->formatStateUsing(function($s) {
                        $s = is_numeric($s) ? (int) $s : 0;
                        $sign = $s < 0 ? '-' : '';
                        return $sign . $this->fmt(abs($s));
                    }),

                TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->getStateUsing(function(Attendance $record) {
                        if (!$record->clock_in_at || !$record->clock_out_at) return 0;
                        return max(0, $record->clock_out_at->diffInSeconds($record->clock_in_at));
                    })
                    ->formatStateUsing(fn($s) => $this->fmt((int)$s)),
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
            ])
            ->striped();
    }
    public function render()
    {
        return view('livewire.backoffice.tables.staff-attendance-table');
    }
}
