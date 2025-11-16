<x-backoffice.layout>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Add Event</h4>
            <div class="text-muted small">Capture the essentials of your next activation</div>
        </div>
        <div>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target=".multi-collapse">
                <i class="bi bi-arrows-collapse"></i>
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('backoffice.events.store') }}" method="POST" class="d-grid gap-3">
        @csrf
        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#event-basic">
                <div class="fw-bold"><i class="bi bi-info-circle text-warning me-2"></i> Basic Information</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="event-basic" class="collapse show multi-collapse">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Title</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                            @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Event Type</label>
                            <select name="event_type" class="form-select" required>
                                <option value="" disabled {{ old('event_type') ? '' : 'selected' }}>Select type</option>
                                @foreach($eventTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('event_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('event_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" class="form-control" name="date" value="{{ old('date') }}" required>
                            @error('date')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Time</label>
                            <input type="time" class="form-control" name="time" value="{{ old('time') }}" required>
                            @error('time')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Location</label>
                            <select name="location" class="form-select">
                                <option value="">Select location</option>
                                @foreach($locationOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('location', $selectedLocation ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Pull from your main venue or assigned floors.</div>
                            @error('location')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Capacity</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="number" class="form-control" name="capacity" min="0" value="{{ old('capacity') }}" placeholder="e.g. 60">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="capacity_unlimited" value="1" id="capacity-unlimited" {{ old('capacity_unlimited') ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="capacity-unlimited">Unlimited</label>
                                </div>
                            </div>
                            @error('capacity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" value="1" name="is_featured" id="is-featured" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is-featured">Featured spotlight</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#event-content">
                <div class="fw-bold"><i class="bi bi-journal-richtext text-warning me-2"></i> Content Sections</div>
                <i class="bi bi-arrow-down"></i>
            </div>
            <hr class="m-0">
            <div id="event-content" class="collapse show multi-collapse">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">About</label>
                        <textarea name="about" class="form-control" rows="4">{{ old('about') }}</textarea>
                        <div class="form-text">Separate bullet points with new lines or prefix them with "-" for customer lists.</div>
                        @error('about')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Highlights</label>
                        <textarea name="event_highlights" class="form-control" rows="4">{{ old('event_highlights') }}</textarea>
                        <div class="form-text">New lines or "-" markers are converted into list items on the customer page.</div>
                        @error('event_highlights')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">What to Expect</label>
                        <textarea name="what_to_expect" class="form-control" rows="4">{{ old('what_to_expect') }}</textarea>
                        <div class="form-text">Keep each expectation on its own line or start with "-" to create bullets.</div>
                        @error('what_to_expect')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('backoffice.events.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Event</button>
        </div>
    </form>
</div>
</x-backoffice.layout>
