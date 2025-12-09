<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Order;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Models\Contracts\HasAvatar;
use Filament\Schemas\Contracts\HasSchemas;

class OrdersTable extends Component implements HasTable, HasSchemas, HasActions
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
        $tenantId = $this->tenantParam;

        return $table
            ->query(
                Order::query()
                    ->with('customerDetail')
                    ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_no')
                    ->label('Ref')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->colors([
                        'primary' => fn ($s) => $s === 'pos',
                        'success' => fn ($s) => $s === 'qr',
                    ])
                    ->sortable(),

                TextColumn::make('order_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => fn ($s) => $s === 'dine-in',
                        'warning' => fn ($s) => $s === 'takeaway',
                    ])
                    ->sortable(),

                TextColumn::make('customerDetail.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => fn ($state) => $state === 'waiting_for_payment',
                        'primary' => fn ($state) => $state === 'confirmed',
                        'info' => fn ($state) => $state === 'preparing',
                        'success' => fn ($state) => $state === 'ready',
                        'danger' => fn ($state) => $state === 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        $map = [
                            'waiting_for_payment' => 'Waiting for Payment',
                            'confirmed' => 'Confirmed',
                            'preparing' => 'Preparing',
                            'ready'     => 'Ready',
                            'cancelled' => 'Cancelled',
                        ];
                        return $map[(string) $state] ?? ucfirst((string) $state);
                    }),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'success' => fn ($p) => $p === 'paid',
                        'warning' => fn ($p) => $p === 'pending',
                        'danger'  => fn ($p) => in_array($p, ['failed', 'cancelled'], true),
                    ])
                    ->formatStateUsing(function ($state) {
                        $map = [
                            'paid'    => 'Paid',
                            'pending' => 'Pending',
                            'failed'  => 'Failed',
                            'cancelled' => 'Cancelled',
                        ];
                        return $map[(string) $state] ?? ucfirst((string) $state);
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'pos' => 'POS',
                        'qr'  => 'Table QR',
                    ]),
                SelectFilter::make('order_type')
                    ->label('Type')
                    ->options([
                        'dine-in' => 'Dine-In',
                        'takeaway' => 'Takeaway',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'waiting_for_payment' => 'Waiting for Payment',
                        'confirmed' => 'Confirmed',
                        'preparing' => 'Preparing',
                        'ready'     => 'Ready',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        'paid'      => 'Paid',
                        'pending'   => 'Pending',
                        'failed'    => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record) => route('backoffice.order.view', [
                        'tenant' => $this->tenantParam,
                        'order' => $record->id,
                    ]))
                    ->extraAttributes(['class' => 'action-btn view-btn-table']),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.orders-table');
    }
}
