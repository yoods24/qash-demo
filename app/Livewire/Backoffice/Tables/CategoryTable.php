<?php

namespace App\Livewire\Backoffice\Tables;

use Livewire\Component;
use App\Models\Category;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;


class CategoryTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public string|int|null $tenantParam = null;

    public function boot(): void
    {
        if($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function table(Table $table): Table 
    {
        return $table
            ->query(Category::query())
            ->headerActions([
                ExportAction::make()
                    ->icon('heroicon-o-document')
                    ->exports([
                        ExcelExport::make('table')
                        ->fromTable()
                        ->except('created_at')
                    ])
            ])
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created On')
                    ->date()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('Delete')
                        ->requiresConfirmation()
                        ->action(fn (Category $record) => $record->each->delete()),
                ])
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->url(fn (Category $record) => route('backoffice.product.edit', [
                        'tenant' => $this->tenantParam,
                        'product' => $record,
                    ]))
                    ->icon('heroicon-o-pencil-square')
                    ->tooltip('Edit')
                    ->extraAttributes([
                        'class' => 'action-btn edit-btn-table',
                    ]),
                Action::make('delete')
                    ->label(null)
                    ->iconButton()
                    ->tooltip('Delete')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function(Category $record) {
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
        return view('livewire.backoffice.tables.category-table');
    }
}
