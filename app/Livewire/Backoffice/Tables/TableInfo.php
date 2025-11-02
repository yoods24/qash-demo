<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice\Tables;

use App\Models\DiningTable;
use App\Models\Floor;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TableInfo extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithSchemas;
    use InteractsWithActions;

    public function table(Table $table): Table
    {
        $tenantId = tenant()?->id ?? request()->route('tenant');

        $query = DiningTable::query()->where('tenant_id', $tenantId)->with('floor');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('label')->label('Table')->searchable()->sortable(),
                TextColumn::make('floor.name')->label('Floor')->sortable(),
                TextColumn::make('status')->badge()->colors([
                    'success' => fn ($state) => $state === 'available',
                    'warning' => fn ($state) => $state === 'oncleaning',
                    'danger' => fn ($state) => $state === 'occupied',
                    'gray' => fn ($state) => $state === 'archived',
                ]),
                TextColumn::make('capacity')->label('Seats')->sortable(),
                TextColumn::make('qr')
                    ->label('QR')
                    ->getStateUsing(function (DiningTable $record) use ($tenantId) {
                        $url = route('backoffice.tables.qr', ['tenant' => $tenantId, 'dining_table' => $record->id]);
                        return '<a href="'.e($url).'" target="_blank">View QR code</a>';
                    })
                    ->html(),
            ])
            ->filters([
                SelectFilter::make('floor_id')
                    ->label('Floor')
                    ->options(function () use ($tenantId) {
                        return Floor::where('tenant_id', $tenantId)
                            ->orderBy('order')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder('All floors')
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.table-info', [
            'tenantId' => tenant()?->id ?? request()->route('tenant'),
        ]);
    }
}
