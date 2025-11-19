<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\CustomerDetail;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class CustomersTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithTable;
    use InteractsWithActions;
    use InteractsWithSchemas;

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
                CustomerDetail::query()
                    ->when($this->tenantParam, fn (Builder $query) => $query->where('tenant_id', $this->tenantParam))
                    ->withCount('orders')
                    ->withMax('orders', 'created_at')
            )
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->toggleable(),
                TextColumn::make('diningTable.label')
                    ->label('Table')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->badge()
                    ->sortable(),
                TextColumn::make('orders_max_created_at')
                    ->label('Last Order')
                    ->dateTime('d M Y H:i')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('has_orders')
                    ->label('Has orders')
                    ->trueLabel('With orders')
                    ->falseLabel('Without orders')
                    ->queries(
                        true: fn (Builder $query) => $query->where('orders_count', '>', 0),
                        false: fn (Builder $query) => $query->where('orders_count', 0),
                    ),
            ])
            ->recordActions([
                Action::make('viewOrders')
                    ->label('View Orders')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->visible(fn ($record) => ($record->orders_count ?? 0) > 0)
                    ->url(fn ($record) => route('backoffice.order.index', [
                        'tenant' => $this->tenantParam,
                        'customer' => $record->id,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.customers-table');
    }
}
