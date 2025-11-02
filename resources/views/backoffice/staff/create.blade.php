<x-backoffice.layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Add Employee</h4>
            <div class="text-muted small">Create new Employee</div>
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

    <form action="{{ route('backoffice.user.store') }}" method="POST" enctype="multipart/form-data" class="d-grid gap-3">
        @csrf
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
            <div id="sec-employee" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                                <div class="avatar-lg rounded-3 d-flex align-items-center justify-content-center bg-light-subtle border py-3 px-4" aria-label="Avatar">
                                    <i class="bi bi-person-fill fs-1 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1 w-100">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                        <div class="w-100 w-lg-auto">
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" name="profile-image" accept="image/*" class="form-control">
                                            @error('profile-image')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="red-asterisk">*</span></label>
                            <input name="firstName" value="{{ old('firstName') }}" class="form-control" required>
                            @error('firstName')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="red-asterisk">*</span></label>
                            <input name="lastName" value="{{ old('lastName') }}" class="form-control" required>
                            @error('lastName')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email <span class="red-asterisk">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contact Number <span class="red-asterisk">*</span></label>
                            <input name="phone" value="{{ old('phone') }}" class="form-control" required>
                            @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Emp Code</label>
                            <input name="emp_code" value="{{ old('emp_code') }}" class="form-control">
                            @error('emp_code')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control">
                            @error('date_of_birth')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="Male" @selected(old('gender')==='Male')>Male</option>
                                <option value="Female" @selected(old('gender')==='Female')>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input name="nationality" value="{{ old('nationality') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Joining Date</label>
                            <input type="date" name="joining_date" value="{{ old('joining_date') }}" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label">Shift</label>
                                <button type="button" class="text-orange" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                                    <i class="bi bi-plus-circle"></i>
                                    Create new shift
                                </button>
                            </div>
                            <select name="shift_id" class="form-select">
                                <option value="">Select</option>
                                @foreach(($shifts ?? []) as $s)
                                    <option value="{{ $s->id }}" @selected(old('shift_id')==$s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">Select</option>
                                @foreach($bloodGroups as $bg)
                                    <option value="{{ $bg }}" @selected(old('blood_group')===$bg)>{{ $bg }}</option>
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
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">About</label>
                            <textarea name="about" rows="3" class="form-control">{{ old('about') }}</textarea>
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
                            <input name="address" value="{{ old('address') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input name="country" value="{{ old('country') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input name="state" value="{{ old('state') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input name="city" value="{{ old('city') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zipcode</label>
                            <input name="zipcode" value="{{ old('zipcode') }}" class="form-control">
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
                        <div class="col-md-4"><label class="form-label">Emergency Contact Number 1</label><input name="emergency_contact_number_1" value="{{ old('emergency_contact_number_1') }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Relation</label><input name="emergency_contact_relation_1" value="{{ old('emergency_contact_relation_1') }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Name</label><input name="emergency_contact_name_1" value="{{ old('emergency_contact_name_1') }}" class="form-control"></div>

                        <div class="col-md-4"><label class="form-label">Emergency Contact Number 2</label><input name="emergency_contact_number_2" value="{{ old('emergency_contact_number_2') }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Relation</label><input name="emergency_contact_relation_2" value="{{ old('emergency_contact_relation_2') }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Name</label><input name="emergency_contact_name_2" value="{{ old('emergency_contact_name_2') }}" class="form-control"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="card">
            <div class="d-flex justify-content-between text-center align-items-center p-3 cursor-pointer"  data-bs-toggle="collapse" data-bs-target="#sec-bank">
                <div class="fw-bold">
                    <i class="bi bi-bank me-2 text-warning"></i> 
                    Address
                </div>
                <i class="bi bi-arrow-down me-2"></i>
            </div>
            <hr class="m-0">
            <div id="sec-bank" class="collapse multi-collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Bank Name</label><input name="bank_name" value="{{ old('bank_name') }}" class="form-control"></div>
                        <div class="col-md-4"><label class="form-label">Account Number</label><input name="account_number" value="{{ old('account_number') }}" class="form-control"></div>
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
                            <label class="form-label">Password <span class="red-asterisk">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="red-asterisk">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <a href="{{ route('backoffice.staff.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-warning text-white">Add Employee</button>
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
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-breaks" type="button" role="tab">Break Timings</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-shift-info" role="tabpanel">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Shift *</label>
                        <input type="text" class="form-control" name="name" required />
                    </div>
                    <div class="col-6">
                        <label class="form-label">From *</label>
                        <input type="time" class="form-control" name="start_time" required />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To *</label>
                        <input type="time" class="form-control" name="end_time" required />
                    </div>
                    <div class="col-12">
                        <label class="form-label">Weekoff</label>
                        <select class="form-select" name="week_off_days[]" multiple>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="7">Sunday</option>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Weekdays Definition</label>
                        <div class="table-responsive border rounded">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Days</th>
                                        <th>All</th>
                                        <th>1st</th>
                                        <th>2nd</th>
                                        <th>3rd</th>
                                        <th>4th</th>
                                        <th>5th</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $dVal => $dLabel)
                                        <tr>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][enabled]" />
                                                    <label class="form-check-label ms-2">{{ $dLabel }}</label>
                                                </div>
                                            </td>
                                            <td><input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][all]" /></td>
                                            @foreach([1,2,3,4,5] as $wk)
                                                <td>
                                                    <input class="form-check-input" type="checkbox" name="day_rules[{{ $dVal }}][weeks][]" value="{{ $wk }}" />
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="recurring" value="1" checked />
                            <label class="form-check-label">Recurring Shift</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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

                    <div class="col-12 mt-2"><strong>Lunch</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[lunch][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[lunch][to]" />
                    </div>

                    <div class="col-12 mt-2"><strong>Evening Break</strong></div>
                    <div class="col-6">
                        <label class="form-label">From</label>
                        <input type="time" class="form-control" name="breaks[evening][from]" />
                    </div>
                    <div class="col-6">
                        <label class="form-label">To</label>
                        <input type="time" class="form-control" name="breaks[evening][to]" />
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" name="description"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-modal.create>
</x-backoffice.layout>

