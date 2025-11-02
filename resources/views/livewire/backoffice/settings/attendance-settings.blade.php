<div>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-tabs" id="attendanceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="att-tab" data-bs-toggle="tab" data-bs-target="#att-pane" type="button" role="tab">Attendance</button>
        </li>
    </ul>
    <div class="tab-content border-start border-end border-bottom p-3">
        <div class="tab-pane fade show active" id="att-pane" role="tabpanel">
            <div class="mb-3">
                <label class="form-label fw-semibold">Attendance Mode</label>
                <div class="d-flex gap-4 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-default-combined" value="default_combined" wire:model="mode">
                        <label class="form-check-label" for="mode-default-combined">Default (Face + Geo)</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-geo" value="geo" wire:model="mode">
                        <label class="form-check-label" for="mode-geo">Geolocation Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-face" value="face" wire:model="mode">
                        <label class="form-check-label" for="mode-face">Facial Recognition Only</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" id="mode-manual" value="manual" wire:model="mode">
                        <label class="form-check-label" for="mode-manual">Manual (Admin sets attendance)</label>
                    </div>
                </div>
                <div class="form-text">Default combines face verification + geofence. Manual disables self clock-in/out for staff.</div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="faorm-label">Enable Facial Recognition</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="face-enabled" wire:model="face_recognition_enabled">
                        <label class="form-check-label" for="face-enabled">{{ $face_recognition_enabled ? 'Enabled' : 'Disabled' }}</label>
                    </div>
                    <div class="form-text">Required for Default or Facial modes.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Apply Facial Recognition To</label>
                    <div class="d-flex gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="face-all" value="all" wire:model="apply_face_to">
                            <label class="form-check-label" for="face-all">All Users</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="face-per-user" value="per_user" wire:model="apply_face_to">
                            <label class="form-check-label" for="face-per-user">Per User Setting</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary" wire:click="saveAttendance">Save Settings</button>
            </div>
        </div>
    </div>
</div>
