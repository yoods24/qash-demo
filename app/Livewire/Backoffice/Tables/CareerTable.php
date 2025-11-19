<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Career;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Livewire\Component;

class CareerTable extends Component implements HasActions, HasSchemas, HasTable
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
                Career::query()
                    ->when($this->tenantParam, fn ($q) => $q->where('tenant_id', $this->tenantParam))
            )
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('salary_range')
                    ->label('Salary')
                    ->sortable()
                    ->toggleable(),

                ToggleColumn::make('status')
                    ->label('Online')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('salary_range')
                    ->label('Salary range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')
                            ->numeric()
                            ->minValue(0)
                            ->label('Min salary'),
                        \Filament\Forms\Components\TextInput::make('max')
                            ->numeric()
                            ->minValue(0)
                            ->label('Max salary'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['min'] ?? null,
                                fn ($q, $min) => $q->where('salary_max', '>=', (int) $min)
                            )
                            ->when(
                                $data['max'] ?? null,
                                fn ($q, $max) => $q->where('salary_min', '<=', (int) $max)
                            );
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->url(fn (Career $record) => route('backoffice.career.edit', [
                        'tenant' => $this->tenantParam,
                        'career' => $record,
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
                    ->action(function (Career $record) {
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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped();
    }

    public function render()
    {
        return view('livewire.backoffice.tables.career-table');
    }
}

