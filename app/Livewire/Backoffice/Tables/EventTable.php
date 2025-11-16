<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Livewire\Component;

class EventTable extends Component implements HasActions, HasSchemas, HasTable
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
                Event::query()->when($this->tenantParam, fn ($query) => $query->where('tenant_id', $this->tenantParam))
            )
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => view('components.backoffice.event-type-badge', [
                        'type' => $state,
                    ])->render())
                    ->html()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->sortable()
                    ->inline(),

                TextColumn::make('is_expired')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Expired' : 'Upcoming')
                    ->color(fn ($state, Event $record) => $record->is_expired ? 'danger' : 'success'),
            ])
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Featured'),

                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options(collect(Event::EVENT_TYPES)->mapWithKeys(fn ($type) => [$type => str($type)->replace('_', ' ')->title()])),

                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('starts_after')
                            ->label('From'),
                        DatePicker::make('ends_before')
                            ->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['starts_after'] ?? null, fn ($q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['ends_before'] ?? null, fn ($q, $date) => $q->whereDate('date', '<=', $date));
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'expired' => 'Expired',
                    ])
                    ->query(function ($query, $state) {
                        return $query->when($state === 'upcoming', fn ($q) => $q->whereDate('date', '>=', now()->toDateString()))
                            ->when($state === 'expired', fn ($q) => $q->whereDate('date', '<', now()->toDateString()));
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->visible(fn () => auth()->check() && auth()->user()->can('create_event'))
                    ->url(fn (Event $record) => route('backoffice.events.edit', [
                        'tenant' => $this->tenantParam,
                        'event' => $record,
                    ]))
                    ->icon('heroicon-o-pencil-square')
                    ->extraAttributes([
                        'class' => 'action-btn edit-btn-table',
                    ]),
                Action::make('delete')
                    ->label('')
                    ->visible(fn () => auth()->check() && auth()->user()->can('delete_event'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        if (! auth()->user()->can('delete_event')) {
                            Notification::make()
                                ->title('You do not have permission to delete events.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title('Event deleted')
                            ->success()
                            ->send();
                    })
                    ->extraAttributes([
                        'class' => 'action-btn delete-btn-table',
                    ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->check() && auth()->user()->can('delete_event')),
                ]),
            ]);
    }

    public function render()
    {
        return view('livewire.backoffice.tables.event-table');
    }
}
