<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qash;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('qash');
        abort_if(!$user, 403);

        $tenants = Tenant::query()
            ->with('admins')
            ->orderByDesc('created_at')
            ->get();

        return view('qash.dashboard', [
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user('qash');
        abort_if(!$user, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'alpha_dash', 'max:80', 'unique:tenants,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'admin_first_name' => ['required', 'string', 'max:120'],
            'admin_last_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'admin_phone' => ['nullable', 'string', 'max:25'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated): void {
            $tenant = Tenant::create([
                'id' => $validated['slug'],
                'data' => array_filter([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ]),
            ]);

            $admin = User::create([
                'firstName' => $validated['admin_first_name'],
                'lastName' => $validated['admin_last_name'],
                'email' => $validated['admin_email'],
                'phone' => $validated['admin_phone'] ?? null,
                'password' => Hash::make($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'status' => 1,
                'is_admin' => true,
            ]);

            $tenant->update([
                'data' => ($tenant->data ?? []) + [
                    'admin_user_id' => $admin->id,
                    'admin_email' => $admin->email,
                ],
            ]);

            if (function_exists('tenancy')) {
                tenancy()->initialize($tenant);
            }

            $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
            $superAdminRole->syncPermissions(Permission::all());
            $admin->assignRole($superAdminRole);

            if (function_exists('tenancy')) {
                tenancy()->end();
            }
        });

        return redirect()
            ->route('qash.dashboard')
            ->with('message', 'Tenant created successfully.');
    }
}
