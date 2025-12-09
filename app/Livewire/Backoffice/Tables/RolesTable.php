<?php

namespace App\Livewire\Backoffice\Tables;

use App\Models\Role;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class RolesTable extends Component implements HasTable, HasActions, HasSchemas
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
    public function table(Table $table):Table {
        return $table
            ->query(Role::query()->where('name', '!=', 'Owner'))
            ->columns([
                TextColumn::make('name')
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('')
                    ->url(fn (Role $role) => route('backoffice.permission.index', [
                        'tenant' => $this->tenantParam,
                        'role' => $role,
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
                    ->action(function(Role $role) {
                        $role->delete();
                        Notification::make()
                            ->title('Role Deleted')
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
        return view('livewire.backoffice.tables.roles-table');
    }
}
