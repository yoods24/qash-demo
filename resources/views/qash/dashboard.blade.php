<x-qash.layout>
    <div class="container-fluid">
        @php($tenants = $tenants ?? collect())
        @if (session('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Create Tenant</h5>
                        <small class="text-muted">Configure a path tenant and its primary admin.</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('qash.tenants.store') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="tenant-name" class="form-label">Tenant name</label>
                                <input type="text" name="name" id="tenant-name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Acme Foods" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="tenant-slug" class="form-label">Path / slug</label>
                                <div class="input-group">
                                    <span class="input-group-text">/t/</span>
                                    <input type="text" name="slug" id="tenant-slug" value="{{ old('slug') }}" class="form-control @error('slug') is-invalid @enderror" placeholder="acme" required>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Used for development URLs such as <code>/t/{slug}</code>.</div>
                            </div>

                            <div class="mb-4">
                                <label for="tenant-description" class="form-label">Notes (optional)</label>
                                <textarea name="description" id="tenant-description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Short description or plan">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <h6 class="mb-3">Primary Admin</h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="admin-first-name" class="form-label">First name</label>
                                    <input type="text" name="admin_first_name" id="admin-first-name" value="{{ old('admin_first_name') }}" class="form-control @error('admin_first_name') is-invalid @enderror" placeholder="Jane" required>
                                    @error('admin_first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="admin-last-name" class="form-label">Last name</label>
                                    <input type="text" name="admin_last_name" id="admin-last-name" value="{{ old('admin_last_name') }}" class="form-control @error('admin_last_name') is-invalid @enderror" placeholder="Doe" required>
                                    @error('admin_last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="admin-email" class="form-label">Email</label>
                                <input type="email" name="admin_email" id="admin-email" value="{{ old('admin_email') }}" class="form-control @error('admin_email') is-invalid @enderror" placeholder="jane@example.com" required>
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="admin-phone" class="form-label">Phone (optional)</label>
                                <input type="text" name="admin_phone" id="admin-phone" value="{{ old('admin_phone') }}" class="form-control @error('admin_phone') is-invalid @enderror" placeholder="+62...">
                                @error('admin_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="admin-password" class="form-label">Password</label>
                                <input type="password" name="admin_password" id="admin-password" class="form-control @error('admin_password') is-invalid @enderror" required>
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="admin-password-confirmation" class="form-label">Confirm password</label>
                                <input type="password" name="admin_password_confirmation" id="admin-password-confirmation" class="form-control" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create tenant</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Tenants</h5>
                            <small class="text-muted">Overview of every tenant currently registered.</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Tenant</th>
                                        <th scope="col">Admin</th>
                                        <th scope="col">Created</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tenants as $tenant)
                                        @php($primaryAdmin = $tenant->admins->first())
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $tenant->name }}</div>
                                                <small class="text-muted">/t/{{ $tenant->path }}</small>
                                            </td>
                                            <td>
                                                @if ($primaryAdmin)
                                                    @php($fullAdminName = trim(($primaryAdmin->firstName ?? '') . ' ' . ($primaryAdmin->lastName ?? '')))
                                                    <div>{{ $fullAdminName !== '' ? $fullAdminName : $primaryAdmin->email }}</div>
                                                    <small class="text-muted">{{ $primaryAdmin->email }}</small>
                                                @else
                                                    <span class="badge bg-warning text-dark">No admin record</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ optional($tenant->created_at)->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url('/t/' . $tenant->path) . '/home'}}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">Visit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                No tenants yet. Create one using the form to get started.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-qash.layout>
