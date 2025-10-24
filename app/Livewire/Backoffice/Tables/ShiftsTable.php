<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Shift;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ShiftsTable extends Component implements HasTable, HasSchemas, HasActions
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
        return $table
            ->query(
                Shift::query()->where('tenant_id', tenant('id'))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Shift Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('timing')
                    ->label('Timing')
                    ->getStateUsing(function(Shift $record): string {
                        $start = \Carbon\Carbon::createFromTimeString($record->start_time)->format('h:i A');
                        $end = \Carbon\Carbon::createFromTimeString($record->end_time)->format('h:i A');
                        return $start . ' - ' . $end;
                    }),

                TextColumn::make('week_off_days')
                    ->label('Week off')
                    ->formatStateUsing(function($state): string {
                        $days = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
                        return collect($state ?? [])->map(fn($d) => $days[$d] ?? $d)->implode(', ');
                    })
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Created On')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state === 'active',
                        'danger' => fn ($state) => $state === 'inactive',
                    ]),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.shifts-table');
    }
}

