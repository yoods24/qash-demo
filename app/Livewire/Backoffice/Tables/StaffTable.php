<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\User;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\CheckboxColumn;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class StaffTable extends Component implements HasTable, HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public string|int|null $tenantParam = null;

    public function boot(): void {
        if ($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function table(Table $table) {
        return $table
            ->query(
                User::query()->with(['roles', 'shift'])
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
            ->columns([
                ImageColumn::make('profile_image_url')
                    ->label('Photo')
                    ->square()
                    ->imageSize(44)
                    ->toggleable(),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->sortable(query: function ($query, $direction) {
                        $query->orderBy('first_name', $direction)
                            ->orderBy('last_name', $direction);
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->where(function ($subQuery) use ($search) {
                            $subQuery->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('role_name')
                    ->label('Role')
                    ->getStateUsing(fn (User $record) => $record->roles->first()->name ?? '—')
                    ->toggleable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('shift.name')
                    ->label('Shift')
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('email'),
                ToggleColumn::make('status')
                    ->sortable()
            ])
                        ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->url(fn (User $record) => route('backoffice.staff.edit', [
                        'tenant' => $this->tenantParam,
                        'staff' => $record,
                    ]))
                    ->icon('heroicon-o-pencil-square')
                    ->extraAttributes([
                        'class' => 'action-btn edit-btn-table',
                    ]),
                Action::make('view')
                    ->label(null)
                    ->iconButton()
                    ->icon('heroicon-o-eye')
                    ->extraAttributes([
                        'class' => 'action-btn view-btn-table'
                        ])
                    ->url(fn (User $record) => route('backoffice.staff.view', [
                        'tenant' => $this->tenantParam,
                        'staff' => $record->id
                    ]))
                    ,
                Action::make('delete')
                    ->label(null)
                    ->iconButton()
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function(User $record) {
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
        return view('livewire.backoffice.tables.staff-table');
    }
}
