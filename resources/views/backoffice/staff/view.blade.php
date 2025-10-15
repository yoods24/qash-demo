<x-backoffice.layout>
    <div class="mb-3 small text-muted">Dashboard / Employees / View</div>

    <!-- Profile Header -->
    <div class="card card-white shadow-sm mb-4 border-0 staff-hero">
        <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
            <div class="avatar-lg rounded-3 d-flex align-items-center justify-content-center bg-light-subtle border" aria-label="Avatar">
                <i class="bi bi-person-fill fs-1 text-secondary"></i>
            </div>
            <div class="flex-grow-1 w-100">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h3 class="mb-1 fw-bold">{{ $staff->name }}</h3>
                        <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">{{ strtoupper($role ?? 'STAFF') }}</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary shadow-sm"><i class="bi bi-upload me-2"></i>Upload New Photo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-pills soft-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-profile" type="button" role="tab">
                <i class="bi bi-person me-2"></i> Profile
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-security" type="button" role="tab">
                <i class="bi bi-shield-lock me-2"></i> Security
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link disabled" type="button" tabindex="-1" aria-disabled="true">
                <i class="bi bi-geo-alt me-2"></i> Address
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link disabled" type="button" tabindex="-1" aria-disabled="true">
                <i class="bi bi-bag me-2"></i> My Orders
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Profile -->
        <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
            <div class="card card-white border-0 shadow-sm">
                <div class="card-header bg-white border-0 border-bottom">
                    <h6 class="mb-0 text-muted fw-semibold">Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-6 d-flex justify-content-between">
                            <div class="fw-semibold">Email</div>
                            <div class="text-primary">{{ $staff->email }}</div>
                        </div>
                        <div class="col-12 col-md-6 d-flex justify-content-between">
                            <div class="fw-semibold">Phone</div>
                            <div class="text-primary">{{ $staff->phone ?? '-' }}</div>
                        </div>
                        <div class="col-12 col-md-6 d-flex justify-content-between">
                            <div class="fw-semibold">Status</div>
                            @if($staff->status)
                                <span class="status-badge online">Active</span>
                            @else
                                <span class="status-badge offline">Inactive</span>
                            @endif
                        </div>
                        <div class="col-12 col-md-6 d-flex justify-content-between">
                            <div class="fw-semibold">Role</div>
                            <div class="text-primary">{{ $role ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security -->
        <div class="tab-pane fade" id="tab-security" role="tabpanel">
            <div class="card card-white border-0 shadow-sm">
                <div class="card-header bg-white border-0 border-bottom">
                    <h6 class="mb-0 text-muted fw-semibold">Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('backoffice.profile.password.update', $staff) }}" class="needs-validation" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label text-uppercase small fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label text-uppercase small fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-12 mt-2">
                                <button class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Simple client-side validation styling
    (function() {
        'use strict';
        document.addEventListener('submit', function(e) {
            const form = e.target.closest('.needs-validation');
            if (!form) return;
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    })();
    </script>
    @endpush

    <style>
        /* Scoped tweaks for the staff view */
        .staff-hero .avatar-lg { width: 120px; height: 120px; }
        .soft-tabs .nav-link { background: #eef2ff; color: #374151; border: 1px solid #e5e7eb; margin-right: .5rem; }
        .soft-tabs .nav-link i { color: #6b7280; }
        .soft-tabs .nav-link.active { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; box-shadow: 0 2px 6px rgba(37,99,235,.15); }
        .soft-tabs .nav-link.active i { color: #2563eb; }
        @media (max-width: 768px) {
            .staff-hero .card-body { padding: 1rem !important; }
            .soft-tabs { overflow-x: auto; white-space: nowrap; }
            .soft-tabs .nav-link { margin-bottom: .5rem; }
        }
    </style>
</x-backoffice.layout>

