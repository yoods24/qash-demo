<x-backoffice.layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Add Employee</h4>
            <div class="text-muted small">Create new Employee</div>
        </div>
        <div class="d-flex gap-2">
            <button id="toggleAll" type="button" class="btn btn-outline-secondary" title="Toggle all sections"><i class="bi bi-arrows-collapse"></i></button>
            <a href="{{ route('backoffice.staff.index') }}" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
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
            <button class="btn text-start p-3 border-0 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#sec-employee" aria-expanded="true">
                <i class="bi bi-person-gear me-2 text-warning"></i> Employee Information
            </button>
            <div id="sec-employee" class="collapse show form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Profile Photo</label>
                            <input type="file" name="profile-image" accept="image/*" class="form-control">
                            @error('profile-image')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name *</label>
                            <input name="firstName" value="{{ old('firstName') }}" class="form-control" required>
                            @error('firstName')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name *</label>
                            <input name="lastName" value="{{ old('lastName') }}" class="form-control" required>
                            @error('lastName')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contact Number *</label>
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
                            <label class="form-label">Shift</label>
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
                                @foreach(['O','A','B','AB'] as $bg)
                                    <option value="{{ $bg }}" @selected(old('blood_group')===$bg)>{{ $bg }}</option>
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
            <button class="btn text-start p-3 border-0 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#sec-address">
                <i class="bi bi-geo-alt me-2 text-warning"></i> Address Information
            </button>
            <div id="sec-address" class="collapse form-section">
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
            <button class="btn text-start p-3 border-0 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#sec-emergency">
                <i class="bi bi-info-circle me-2 text-warning"></i> Emergency Information
            </button>
            <div id="sec-emergency" class="collapse form-section">
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
            <button class="btn text-start p-3 border-0 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#sec-bank">
                <i class="bi bi-bank me-2 text-warning"></i> Bank Information
            </button>
            <div id="sec-bank" class="collapse form-section">
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
            <button class="btn text-start p-3 border-0 bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#sec-password">
                <i class="bi bi-info-circle me-2 text-warning"></i> Password
            </button>
            <div id="sec-password" class="collapse form-section">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
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

    @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('toggleAll');
        const sections = document.querySelectorAll('.form-section');
        const asCollapse = (el) => new bootstrap.Collapse(el, { toggle: false });
        btn?.addEventListener('click', function() {
          const anyOpen = Array.from(sections).some(el => el.classList.contains('show'));
          sections.forEach(el => asCollapse(el)[anyOpen ? 'hide' : 'show']());
        });
      });
    </script>
    @endpush
</x-backoffice.layout>
