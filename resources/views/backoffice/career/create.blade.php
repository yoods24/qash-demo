<x-backoffice.layout>
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Create Career</h4>
        <div class="text-muted small">Add a new open position</div>
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

    <form action="{{ route('backoffice.career.store') }}" method="POST" class="d-grid gap-3">
      @csrf

      <div class="row g-3">
        <!-- Left column: main content -->
        <div class="col-md-8">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <label for="title" class="form-label fw-bold">Job Title</label>
                <input
                  type="text"
                  class="form-control"
                  id="title"
                  name="title"
                  value="{{ old('title') }}"
                  required
                >
              </div>

              <div class="mb-3">
                <label for="about" class="form-label fw-bold">About</label>
                <textarea
                  id="about"
                  name="about"
                  class="form-control"
                  rows="4"
                  required
                >{{ old('about') }}</textarea>
                <small class="text-muted">Short summary shown on the listing page.</small>
              </div>

              <div class="mb-3">
                <label for="responsibilities" class="form-label fw-bold">Responsibilities</label>
                <textarea
                  id="responsibilities"
                  name="responsibilities"
                  class="form-control"
                  rows="5"
                >{{ old('responsibilities') }}</textarea>
                <small class="text-muted">Use separate lines or bullets for each responsibility.</small>
              </div>

              <div class="mb-3">
                <label for="requirements" class="form-label fw-bold">Requirements</label>
                <textarea
                  id="requirements"
                  name="requirements"
                  class="form-control"
                  rows="5"
                >{{ old('requirements') }}</textarea>
                <small class="text-muted">Use separate lines or bullets for each requirement.</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Right column: settings -->
        <div class="col-md-4">
          <div class="card">
            <div class="card-body d-grid gap-3">
              <div>
                <label for="salary_min" class="form-label fw-bold">Minimum Salary (Rp)</label>
                <input
                  type="number"
                  class="form-control"
                  id="salary_min"
                  name="salary_min"
                  min="0"
                  value="{{ old('salary_min') }}"
                  required
                >
              </div>

              <div>
                <label for="salary_max" class="form-label fw-bold">Maximum Salary (Rp)</label>
                <input
                  type="number"
                  class="form-control"
                  id="salary_max"
                  name="salary_max"
                  min="0"
                  value="{{ old('salary_max') }}"
                  required
                >
              </div>

              <div>
                <label for="status" class="form-label fw-bold">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="Online" {{ old('status') === 'Online' ? 'selected' : '' }}>Online</option>
                  <option value="Offline" {{ old('status') === 'Offline' ? 'selected' : '' }}>Offline</option>
                </select>
                <small class="text-muted">Online careers are visible to customers.</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('backoffice.careers.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create Career</button>
      </div>
    </form>
  </div>
</x-backoffice.layout>
