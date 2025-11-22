<?php

namespace App\Livewire\Backoffice\Tables;

use Filament\Tables;
use App\Models\Product;
use Livewire\Component;
use App\Models\Discount;
use Filament\Tables\Table;
use function Livewire\wrap;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class DiscountTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithSchemas;
    use InteractsWithActions;

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
                Discount::query()
                    ->when($this->tenantParam, fn (Builder $query) => $query->where('tenant_id', $this->tenantParam))
            )
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),

                TextColumn::make('value')
                    ->label('Value')
                    ->formatStateUsing(fn ($state, Discount $record) =>
                        $record->discount_type === 'percent'
                            ? number_format($record->value, 2).'%' 
                            : rupiah($record->value)
                    ),

                TextColumn::make('validity')
                    ->label('Validity')
                    ->getStateUsing(fn (Discount $record) =>
                        optional($record->valid_from)->format('d M Y').' – '.
                        optional($record->valid_till)->format('d M Y')
                    ),

                TextColumn::make('days')
                    ->label('Days')
                    ->listWithLineBreaks()
                    ->limitList()
                    ->wrap(),
                    
                TextColumn::make('products')
                    ->label('Products')
                    ->formatStateUsing(function ($state, Discount $record) {

                        // All products
                        if ($record->applicable_for === "all") {
                            return 'All Products';   // <-- return simple string, not array
                        }

                        // If JSON stored as string
                        if (is_string($state)) {
                            $state = json_decode($state, true) ?? [];
                        }

                        // Ensure pure array of integer IDs
                        $ids = collect($state)
                            ->flatten()
                            ->unique()
                            ->values()
                            ->toArray();

                        // Fetch names
                        $names = Product::whereIn('id', $ids)->pluck('name')->toArray();

                        // ⛔ IMPORTANT:
                        // Return the names as a simple comma-separated string,
                        // so Filament doesn't wrap them.
                        return implode(', ', $names);
                    })
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->color(fn ($state) => $state === 'active' ? 'success' : 'gray'),
            ])
            ->recordActions([
                // Create Promo Event button
                Action::make('createPromoEvent')
                    ->label('')
                    ->tooltip('Create Promo Event')
                    ->icon('heroicon-o-sparkles')
                    ->url(fn (Discount $record) => route('backoffice.events.create', [
                        'tenant' => $this->tenantParam,
                        'from_discount' => true,
                        'title' => $record->name,
                        'event_type' => 'Promo',
                        'date_from' => optional($record->valid_from)->format('Y-m-d'),
                        'date_till' => optional($record->valid_till)->format('Y-m-d'),
                    ]))
                    ->extraAttributes(['class' => 'action-btn view-btn-table']),

                // Edit (opens modal form rendered on page)
                Action::make('edit')
                    ->label('')
                    ->tooltip('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->extraAttributes(function (Discount $record) {
                        $payload = [
                            'id' => $record->id,
                            'name' => $record->name,
                            'quantity_type' => $record->quantity_type,
                            'quantity' => $record->quantity,
                            'applicable_for' => $record->applicable_for,
                            'products' => collect($record->products ?? [])->map(fn ($id) => (int) $id)->all(),
                            'valid_from' => optional($record->valid_from)->format('Y-m-d'),
                            'valid_till' => optional($record->valid_till)->format('Y-m-d'),
                            'discount_type' => $record->discount_type,
                            'value' => (float) $record->value,
                            'status' => $record->status,
                            'days' => collect($record->days ?? [])->map(fn ($day) => strtolower((string) $day))->all(),
                        ];

                        return [
                            'class' => 'action-btn edit-btn-table',
                            'data-discount' => htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8'),
                            'data-update-action' => route('backoffice.discounts.update', [
                                'tenant' => $this->tenantParam,
                                'discount' => $record,
                            ]),
                        ];
                    }),

                Action::make('delete')
                    ->label(null)
                    ->iconButton()
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->action(function(Discount $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Record Deleted')
                            ->success()
                            ->send();
                    })
                    ->color('danger')
                    ->extraAttributes(['class' => 'action-btn delete-btn-table']),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.discount-table');
    }
}
