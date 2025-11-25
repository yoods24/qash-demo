<div class="card">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-3">
            <div>
                <h5 class="mb-1">Staff Attendance</h5>
                <p class="text-muted mb-0">Records for {{ $this->selectedDateLabel }}</p>
            </div>
            <div class="w-100" style="max-width: 240px;">
                <label class="form-label mb-1 text-muted small text-uppercase fw-semibold">Pick a date</label>
                <input type="date"
                       wire:model.live="selectedDate"
                       class="form-control"
                       max="{{ now()->toDateString() }}">
            </div>
        </div>

        <div id="fi-admin-attendance-table">
            {{ $this->table }}
        </div>
    </div>
</div>
