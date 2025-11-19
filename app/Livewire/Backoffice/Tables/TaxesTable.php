<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Tax;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class TaxesTable extends Component implements HasTable, HasActions, HasSchemas
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
                Tax::query()
                    ->when($this->tenantParam, fn (Builder $query) => $query->where('tenant_id', $this->tenantParam))
            )
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Tax Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('rate')
                    ->label('Rate')
                    ->formatStateUsing(function (float $state, Tax $record) {
                        $value = number_format($state, 2);
                        return $record->type === 'percentage'
                            ? "{$value}%"
                            : rupiah($state);
                    })
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Only active')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Tax')
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Update Tax')
                    ->extraAttributes(['class' => 'action-btn edit-btn-table'])
                    ->form($this->formSchema())
                    ->action(fn (Tax $record, array $data) => $record->update($data)),
                Action::make('delete')
                    ->label(null)
                    ->iconButton()
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function (Tax $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Career deleted')
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

    private function formSchema(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(120),
            \Filament\Forms\Components\Select::make('type')
                ->label('Type')
                ->options([
                    'percentage' => 'Percentage',
                    'fixed' => 'Fixed Amount',
                ])
                ->required()
                ->native(false),
            \Filament\Forms\Components\TextInput::make('rate')
                ->label('Rate')
                ->numeric()
                ->required()
                ->suffix(fn ($state, callable $get) => $get('type') === 'fixed' ? 'IDR' : '%'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.tables.taxes-table');
    }
}
