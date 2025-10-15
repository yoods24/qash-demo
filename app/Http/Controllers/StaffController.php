<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use \Spatie\Permission\Models\Permission;
class StaffController extends Controller
{
    public function index() {
        $staffs = User::where('is_admin', 0)->with('roles')->paginate(10); // eager load roles
        $roles = Role::select('name')->pluck('name');
        return view('backoffice.staff.index', data: ['staffs' => $staffs , 'roles' => $roles]);
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
        $roles = Role::all();
        return view('backoffice.staff.roles', ['roles' => $roles]);
    }

    public function storeRole(Request $request) {
        Role::firstOrCreate(['name' => $request['role']]);
        return redirect()->route('backoffice.roles.index')->with('message', 'Role successfully Created!');
    }

    public function destroyRole(Role $role) {
        $role->delete();
        return redirect()->route('backoffice.roles.index')->with('message', 'Role successfully Deleted!');
    }

    public function storeStaff(Request $request) {
        // needs admin role

        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:users,name'],
            'email' => ['required', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
        ]);
// DEEEEBUUUGGGGGGG ROLE
        $user = User::firstOrCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password'])
        ]);
        $user->assignRole($request->role);

        return redirect()->route('backoffice.staff.index')
                         ->with('message', 'Staff successfully Created!');
    }

    public function indexPermission(Role $role) {
        $permissions = Permission::all();

        // Group permissions based on the module prefix
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return ucfirst(explode('_', $permission->name)[0]);
        });

        return view('backoffice.staff.role-create', compact('role', 'groupedPermissions'));
    }

    public function updatePermission(Request $request, Role $role) {
        Role::where('id', $role->id)->update(['name' => $request->role]);
        
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);
        return redirect()->route('backoffice.roles.index')
                         ->with('message', 'Role Updated Successfully!');
    }
}
