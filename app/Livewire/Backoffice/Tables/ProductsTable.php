<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Filters\TernaryFilter;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ProductsTable extends Component implements HasActions, HasSchemas, HasTable
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
                Product::query()->with('category')
            )
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-document')
                    ->extraAttributes([
                        'class' => 'rounded'
                    ])
                    ->exports([
                        ExcelExport::make('table')
                            ->fromTable()
                            ->except('created_at', 'product_image', 'featured')
                            ->ignoreFormatting()
                    ])
            ])
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Image')
                    ->url(tenant_asset(''))
                    ->square()
                    ->imageSize(44)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),

                ToggleColumn::make('featured')
                    ->label('Featured')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ])
            ])

            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                TernaryFilter::make('featured')
                    ->label('Featured'),
            ])

            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->url(fn (Product $record) => route('backoffice.product.edit', [
                        'tenant' => $this->tenantParam,
                        'product' => $record,
                    ]))
                    ->icon('heroicon-o-pencil-square')
                    ->extraAttributes([
                        'class' => 'action-btn edit-btn-table',
                    ]),
                Action::make('delete')
                    ->label(null)
                    ->iconButton()
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function(Product $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Record Deleted')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-trash')
                    ->extraAttributes([
                        'class' => 'action-btn delete-btn-table',
                    ]),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.products-table');
    }
}
