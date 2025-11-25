<?php

namespace App\Http\Controllers;

use App\Http\Enums\BloodGroup;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffPhotoRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\Shift;
use Illuminate\Http\Request;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;
use App\Services\Staff\StaffService;

class StaffController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function index() {
        $staffs = User::where('tenant_id', tenant('id'))
            ->where('is_admin', 0)
            ->with('roles')
            ->paginate(10);
        $roles = Role::select('name')->pluck('name');
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
        $roles = Role::all();
        $bloodGroups = BloodGroup::cases();
        return view('backoffice.staff.create', compact(['shifts', 'roles', 'bloodGroups']));
    }

    public function createFull()
    {
        return $this->create();
    }
    public function view(User $staff)
    {
        $role = $staff->getRoleNames()->first();
        return view('backoffice.staff.view', [
            'staff' => $staff,
            'role' => $role,
        ]);
    }
    public function updatePhoto(UpdateStaffPhotoRequest $request, User $staff)
    {
        if ($staff->tenant_id !== tenant('id')) {
            abort(403);
        }

        $this->staffService->updatePhoto($staff, $request->file('profile-image'));

        return back()->with('message', 'Profile photo updated.');
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

    public function storeRoleWr(Request $request) {
        Role::firstOrCreate(['name' => $request['role']]);
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
    public function storeFull(StoreStaffRequest $request) {
        $this->staffService->createStaff(
            $request->validated(),
            $request->file('profile-image'),
            $request->input('role')
        );

        return redirect()->route('backoffice.staff.index')
            ->with('message', 'Employee created successfully.');
    }

    public function editFull(User $staff)
    {
        $staff->load('roles');

        $shifts = Shift::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $roles = Role::all();
        $bloodGroups = BloodGroup::cases();

        return view('backoffice.staff.edit', compact('staff', 'shifts', 'roles', 'bloodGroups'));
    }

    public function updateFull(UpdateStaffRequest $request, User $staff)
    {
        $this->staffService->updateStaff(
            $staff,
            $request->validated(),
            $request->file('profile-image'),
            $request->input('role')
        );

        return redirect()->route('backoffice.staff.index')
            ->with('message', 'Employee updated successfully.');
    }

    public function indexPermission(Role $role) {
        $permissions = Permission::all();

        // Group permissions based on the module prefix
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return ucfirst(explode('_', $permission->name)[0]);
        });

        return view('backoffice.staff.update-permission', compact('role', 'groupedPermissions'));
    }

    public function updatePermission(Request $request, Role $role) {
        Role::where('id', $role->id)->update(['name' => $request->role]);
        
        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);
        return redirect()->route('backoffice.roles.index')
                         ->with('message', 'Role Updated Successfully!');
    }
}
