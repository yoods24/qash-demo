<x-backoffice.layout>

<div class="container">
    <h2>Manage Permissions for Role:</h2>

    <form method="POST" action="{{ route('backoffice.permission.update', $role->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <input type="text" name="role" id="role" class="form-control" value="{{ $role->name }}">
        </div>
        @foreach ($groupedPermissions as $group => $permissions)
            <div class="mb-4 border p-3">
                <h4 class="sekunder">{{ $group }}</h4>
                <div class="row ">
                    @foreach ($permissions as $permission)
                        @php
                            $parts = explode('_', $permission->name);
                            $action = ucfirst($parts[1] ?? '');
                        @endphp

                        <div class="col-md-3 m-3 permission-border">
                            <label class="form-label d-block">{{ $action }}</label>
                            <label class="switch">
                                <input 
                                    type="checkbox" 
                                    name="permissions[]" 
                                    value="{{ $permission->name }}" 
                                    {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                >
                                <span class="slider"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primer">Save Permissions</button>
    </form>
</div>

<!-- Modal -->
</x-backoffice.layout>
