<x-backoffice.layout>
        <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Employee</h4>
            <div class="text-muted small">Update {{ trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) }}</div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse"  data-bs-target=".multi-collapse" title="Toggle all sections"><i class="bi bi-arrows-collapse"></i></button>
            <a href="{{ route('backoffice.staff.index') }}" class="btn btn-primer"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please correct the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $dateOfBirth = $staff->date_of_birth ? \Illuminate\Support\Carbon::parse($staff->date_of_birth)->format('Y-m-d') : null;
        $joiningDate = $staff->joining_date ? \Illuminate\Support\Carbon::parse($staff->joining_date)->format('Y-m-d') : null;
    @endphp

    <form action="{{ route('backoffice.staff.update', $staff) }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
        @csrf
        @method('PUT')
        <!-- Employee Information -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-employee" aria-expanded="false">
                <div class="fw-bold">
                    <i class="bi bi-person-gear me-2 text-warning"></i>
                    Employee Information
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-employee" class="collapse multi-collapse form-section show">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                                <div class="avatar-lg rounded-3 d-flex align-items-center justify-content-center bg-light-subtle border py-3 px-4 overflow-hidden" aria-label="Avatar">
                                    @if($staff->profile_image_url)
                                        <img src="{{ $staff->profile_image_url }}" alt="{{ trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? '')) }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <i class="bi bi-person-fill fs-1 text-secondary"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1 w-100">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                        <div class="w-100 w-lg-auto">
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" name="profile_image" accept="image/*" class="form-control">
                                            @error('profile_image')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="red-asterisk">*</span></label>
                            <input name="first_name" value="{{ old('first_name', $staff->first_name) }}" class="form-control" required>
                            @error('first_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="red-asterisk">*</span></label>
                            <input name="last_name" value="{{ old('last_name', $staff->last_name) }}" class="form-control" required>
                            @error('last_name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email <span class="red-asterisk">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $staff->email) }}" class="form-control" required>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contact Number <span class="red-asterisk">*</span></label>
                            <input name="phone" value="{{ old('phone', $staff->phone) }}" class="form-control" required>
                            @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Emp Code</label>
                            <input name="emp_code" value="{{ old('emp_code', $staff->emp_code) }}" class="form-control">
                            @error('emp_code')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $dateOfBirth) }}" class="form-control">
                            @error('date_of_birth')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                @foreach(['Male','Female'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender', $staff->gender) === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                            @error('gender')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input name="nationality" value="{{ old('nationality', $staff->nationality) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" value="{{ old('joining_date', $joiningDate) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shift</label>
                            <select name="shift_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(old('shift_id', $staff->shift_id) == $shift->id)>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            @error('shift_id')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">Select</option>
                                @foreach($bloodGroups as $group)
                                    <option value="{{ $group->value }}" @selected(old('blood_group', $staff->blood_group?->value) == $group->value)>{{ $group->value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Role</label>
                                <button type="button" class="text-orange" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="bi bi-plus-circle"></i>
                                    Create new role
                                </button>
                            </div>
                            <select name="role" class="form-select">
                                <option value="">Select</option>
                                @php $currentRole = old('role', $staff->roles->first()->name ?? ''); @endphp
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" @selected($currentRole === $role->name)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">About</label>
                            <textarea name="about" rows="3" class="form-control">{{ old('about', $staff->about) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-address">
                <div class="fw-bold">
                    <i class="bi bi-geo-alt me-2 text-warning"></i>
                    Address
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-address" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input name="address" value="{{ old('address', $staff->address) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input name="country" value="{{ old('country', $staff->country) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input name="state" value="{{ old('state', $staff->state) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input name="city" value="{{ old('city', $staff->city) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zipcode</label>
                            <input name="zipcode" value="{{ old('zipcode', $staff->zipcode) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Information -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-emergency">
                <div class="fw-bold">
                    <i class="bi bi-telephone-plus me-2 text-warning"></i>
                    Emergency
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-emergency" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Emergency Contact Number 1</label><input name="emergency_contact_number_1" value="{{ old('emergency_contact_number_1', $staff->emergency_contact_number_1) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Relation</label><input name="emergency_contact_relation_1" value="{{ old('emergency_contact_relation_1', $staff->emergency_contact_relation_1) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Name</label><input name="emergency_contact_name_1" value="{{ old('emergency_contact_name_1', $staff->emergency_contact_name_1) }}" class="form-control"></div>

                        <div class="col-md-4"><label class="form-label">Emergency Contact Number 2</label><input name="emergency_contact_number_2" value="{{ old('emergency_contact_number_2', $staff->emergency_contact_number_2) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Relation</label><input name="emergency_contact_relation_2" value="{{ old('emergency_contact_relation_2', $staff->emergency_contact_relation_2) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Name</label><input name="emergency_contact_name_2" value="{{ old('emergency_contact_name_2', $staff->emergency_contact_name_2) }}" class="form-control"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-bank">
                <div class="fw-bold">
                    <i class="bi bi-bank me-2 text-warning"></i>
                    Bank
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-bank" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Bank Name</label><input name="bank_name" value="{{ old('bank_name', $staff->bank_name) }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Account Number</label><input name="account_number" value="{{ old('account_number', $staff->account_number) }}" class="form-control"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-password">
                <div class="fw-bold">
                    <i class="bi bi-braces-asterisk me-2 text-warning"></i>
                    Password
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-password" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                            @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm new password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('backoffice.staff.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-warning text-white">Save Changes</button>
        </div>
    </form>

    <x-modal.create id="addProductModal" title="Tambah Role" action="{{ route('backoffice.role.store-wr') }}">
        <div class="mb-3">
            <label for="product" class="form-label">Add Role</label>
            <input type="text" class="form-control" id="role" name="role" placeholder="Masukkan Role">
        </div>
    </x-modal.create>

    <x-modal.create id="addShiftModal" title="Add Shift" action="{{ route('backoffice.shift.store') }}">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-shift-info" type="button" role="tab">Shift Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-breaks" type="button" role="tab">Break Rules</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-shift-info" role="tabpanel">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Shift Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Fixed 9-6" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="starts_at" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="ends_at" />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Optional"></textarea>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-breaks" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12"><strong>Morning Break</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[morning][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[morning][to]" />
                    </div>
                </div>
            </div>
        </div>
    </x-modal.create>
</x-backoffice.layout>
