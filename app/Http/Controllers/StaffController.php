<?php

namespace App\Http\Controllers;

use App\Http\Enums\BloodGroup;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;
class StaffController extends Controller
{
    public function index() {
        $staffs = User::where('tenant_id', tenant('id'))
            ->where('is_admin', 0)
            ->with('roles')
            ->paginate(10);
        $roles = \App\Models\Role::select('name')->pluck('name');
        $staffCount = User::where('tenant_id', tenant('id'))
            ->where('is_admin', 0)
            ->count();
        return view('backoffice.staff.index', [
            'staffs' => $staffs,
            'roles' => $roles,
            'staffCount' => $staffCount,
        ]);
    }

    // New full user create page (collapsible sections)
    public function create() {
        $shifts = Shift::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $roles = \App\Models\Role::all();
        $bloodGroups = BloodGroup::cases();
        return view('backoffice.staff.create', compact(['shifts', 'roles', 'bloodGroups']));
    }
    public function view(User $staff)
    {
        $role = $staff->getRoleNames()->first();
        return view('backoffice.staff.view', [
            'staff' => $staff,
            'role' => $role,
        ]);
    }

    public function destroy(User $staff) {
        $staff->delete();
        return redirect()->route('backoffice.staff.index')->with('message', 'Staff successfully deleted!');
    }

    public function indexRoles() {
        $roles = \App\Models\Role::all();
        return view('backoffice.staff.roles', ['roles' => $roles]);
    }

    public function storeRole(Request $request) {
        \App\Models\Role::firstOrCreate(['name' => $request['role']]);
        return redirect()->route('backoffice.roles.index')->with('message', 'Role successfully Created!');
    }    

    public function storeRoleWr(Request $request) {
        \App\Models\Role::firstOrCreate(['name' => $request['role']]);
        Notification::make()
            ->title('Success')
            ->body('Role Successfully created!')
            ->success()
            ->send();
        return back();
    }

    public function destroyRole(Role $role) {
        $role->delete();
        return redirect()->route('backoffice.roles.index')->with('message', 'Role successfully Deleted!');
    }

    public function storeStaff(Request $request) {
        // needs admin role

        $validated = $request->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);
// DEEEEBUUUGGGGGGG ROLE
        $user = User::firstOrCreate([
            'email' => $validated['email'],
        ], [
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);
        $user->assignRole($request->role);

        return redirect()->route('backoffice.staff.index')
                         ->with('message', 'Staff successfully Created!');
    }

    // Store from the new full create form
    public function storeFull(Request $request) {
        $validated = $request->validate([
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'emp_code' => ['nullable', 'string', 'max:120', 'unique:users,emp_code'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Male,Female'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'joining_date' => ['nullable', 'date'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'blood_group' => ['nullable', 'in:O,A,B,AB'],
            'about' => ['nullable', 'string', 'max:500'],
            // Address
            'address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'zipcode' => ['nullable', 'string', 'max:30'],
            // Emergency
            'emergency_contact_number_1' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation_1' => ['nullable', 'string', 'max:60'],
            'emergency_contact_name_1' => ['nullable', 'string', 'max:120'],
            'emergency_contact_number_2' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation_2' => ['nullable', 'string', 'max:60'],
            'emergency_contact_name_2' => ['nullable', 'string', 'max:120'],
            // Bank
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:60'],
            // Password
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Profile image
            'profile-image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = new User($validated);
        $user->tenant_id = tenant('id');
        $user->is_admin = 0;
        $user->assignRole($request->role);
        $user->status = 1;
        if (!$request->hasFile('profile-image')) {
            // Ensure non-nullable column gets a default value
            $user->setAttribute('profile-image', '');
        }
        $user->save();

        // Handle profile image upload (store within tenant's public disk, namespaced by user)
        $imagePath = null;
        if ($request->hasFile('profile-image')) {
            // Store under users/{user_id}/... on the tenant-scoped 'public' disk
            $imagePath = $request->file('profile-image')->store('users/' . $user->id, 'public');
            $user->setAttribute('profile-image', $imagePath);
            $user->save();
        }

        return redirect()->route('backoffice.staff.index')
            ->with('message', 'Employee created successfully.');
    }

    public function indexPermission(\App\Models\Role $role) {
        $permissions = \App\Models\Permission::all();

        // Group permissions based on the module prefix
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return ucfirst(explode('_', $permission->name)[0]);
        });

        return view('backoffice.staff.update-permission', compact('role', 'groupedPermissions'));
    }

    public function updatePermission(Request $request, \App\Models\Role $role) {
        \App\Models\Role::where('id', $role->id)->update(['name' => $request->role]);
        
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);
        return redirect()->route('backoffice.roles.index')
                         ->with('message', 'Role Updated Successfully!');
    }
}
