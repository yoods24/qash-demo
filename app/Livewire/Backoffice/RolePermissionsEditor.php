<?php

declare(strict_types=1);

namespace App\Livewire\Backoffice;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Arr;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionsEditor extends Component
{
    public Role $role;

    public string $roleName = '';

    /**
     * Map of permission name => bool (assigned?)
     * @var array<string,bool>
     */
    public array $state = [];

    /** @var array<string,bool> */
    protected array $available = [];
    public string|int|null $tenantParam = null;

    public function boot(): void
    {
        if ($this->tenantParam === null) {
            $this->tenantParam = request()->route('tenant') ?? (function_exists('tenant') ? tenant('id') : null);
        }
    }
    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->roleName = $role->name;

        $names = $this->allPermissionNamesFromTree();
        $currentAssignments = $this->role->permissions->pluck('name')->all();

        // Ensure the editor always exposes every permission in the tree,
        // even if the tenant hasn't seeded it yet.
        $existing = Permission::query()->whereIn('name', $names)->pluck('name')->all();
        foreach ($names as $name) {
            $this->available[$name] = in_array($name, $existing, true);
            $this->state[$name] = in_array($name, $currentAssignments, true);
        }
    }

    public function render()
    {
        return view('livewire.backoffice.role-permissions-editor', [
            'modules' => $this->modules(),
        ]);
    }

    public function updatedState($value, $key): void
    {
        // Handle parent-child relationships: turning off a parent disables children
        foreach ($this->modules() as $mod) {
            $parent = $mod['view'];
            if ($key === $parent && empty($this->state[$parent])) {
                foreach ($mod['children'] as $child) {
                    $cv = $child['view'];
                    $this->state[$cv] = false;
                    foreach ($child['actions'] as $action) {
                        $this->state[$action['name']] = false;
                    }
                }
            }

            // Child toggled off -> turn off its actions
            foreach ($mod['children'] as $child) {
                $cv = $child['view'];
                if ($key === $cv && empty($this->state[$cv])) {
                    foreach ($child['actions'] as $action) {
                        $this->state[$action['name']] = false;
                    }
                }
            }
        }
    }
    public function cancel() {
        
    }
    public function save(): void
    {
        // Update role name if changed
        if (trim($this->roleName) !== '' && $this->role->name !== $this->roleName) {
            $this->role->name = $this->roleName;
            $this->role->save();
        }

        // Only sync permissions declared in this editor; preserve others
        $managed = $this->allPermissionNamesFromTree();

        // Determine selected managed permissions from current UI state
        $selectedManaged = array_keys(array_filter($this->state, fn($v) => (bool) $v));
        $selectedManaged = array_values(array_intersect($selectedManaged, $managed));

        // Ensure all selected managed permissions exist for this tenant
        $guard = $this->role->guard_name ?? 'web';
        foreach ($selectedManaged as $permName) {
            Permission::firstOrCreate(['name' => $permName], ['guard_name' => $guard]);
        }

        // Keep any existing permissions not managed by this editor
        $current = $this->role->permissions->pluck('name')->all();
        $unmanaged = array_values(array_diff($current, $managed));
        $final = array_values(array_unique(array_merge($unmanaged, $selectedManaged)));

        $this->role->syncPermissions($final);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        redirect()->route('backoffice.roles.index', ['tenant' => $this->tenantParam])
        ->with('message', 'Permission for role updated successfully.');
    }

    public function enableAll(): void
    {
        foreach ($this->modules() as $mod) {
            $this->enableModuleByDef($mod);
        }
    }

    public function disableAll(): void
    {
        foreach ($this->modules() as $mod) {
            $this->setState($mod['view'], false);
            foreach ($mod['children'] as $child) {
                $this->setState($child['view'], false);
                foreach ($child['actions'] as $action) {
                    $this->setState($action['name'], false);
                }
            }
        }
    }

    public function enableModule(string $moduleKey): void
    {
        foreach ($this->modules() as $mod) {
            if ($mod['key'] === $moduleKey) {
                $this->enableModuleByDef($mod);
                break;
            }
        }
    }

    public function enableChild(string $moduleKey, string $childKey): void
    {
        foreach ($this->modules() as $mod) {
            if ($mod['key'] !== $moduleKey) {
                continue;
            }
            // Ensure parent is enabled
            $this->setState($mod['view'], true);
            foreach ($mod['children'] as $child) {
                if ($child['key'] !== $childKey) {
                    continue;
                }
                $this->setState($child['view'], true);
                foreach ($child['actions'] as $action) {
                    $this->setState($action['name'], true);
                }
                break 2;
            }
        }
    }

    private function enableModuleByDef(array $mod): void
    {
        $this->setState($mod['view'], true);
        foreach ($mod['children'] as $child) {
            $this->setState($child['view'], true);
            foreach ($child['actions'] as $action) {
                $this->setState($action['name'], true);
            }
        }
    }

    private function setState(string $name, bool $value): void
    {
        $this->state[$name] = $value;
    }

    /**
     * Describe the modules -> sections -> actions tree for UI.
     * Each node declares the permission names to bind to.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function modules(): array
    {
        return config('tenant_permissions.modules', []);
    }

    /**
     * Collect list of all permission names declared in modules() tree.
     *
     * @return array<int,string>
     */
    protected function allPermissionNamesFromTree(): array
    {
        $names = [];
        foreach ($this->modules() as $mod) {
            $names[] = $mod['view'];
            foreach ($mod['children'] as $child) {
                $names[] = $child['view'];
                foreach ($child['actions'] as $action) {
                    $names[] = $action['name'];
                }
            }
        }
        return $names;
    }
}
