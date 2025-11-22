<x-backoffice.layout>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Event</h4>
            <div class="text-muted small">Update the experience details</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.events.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target=".multi-collapse" title="Toggle sections">
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

    @php
        $unlimitedCapacity = (bool) old('capacity_unlimited', $event->capacity === null);
        $formDefaults = $formDefaults ?? [];
        $useDateRange = (bool) old('use_date_range', $formDefaults['use_date_range'] ?? false);
        $singleDateValue = old('event_date', $formDefaults['event_date'] ?? '');
        $rangeStartValue = old('date_from', $formDefaults['date_from'] ?? '');
        $rangeEndValue = old('date_till', $formDefaults['date_till'] ?? '');
    @endphp

    <form action="{{ route('backoffice.events.update', $event) }}" method="POST" class="d-grid gap-3">
        @csrf
        @method('PUT')
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
                            <input type="text" class="form-control" name="title" value="{{ old('title', $event->title) }}" required>
                            @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Event Type</label>
                            <select name="event_type" class="form-select" required>
                                @foreach($eventTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('event_type', $event->event_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('event_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-bold mb-0">Schedule</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="use_date_range" value="0">
                                    <input class="form-check-input" type="checkbox" value="1" name="use_date_range" id="use-date-range" {{ $useDateRange ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="use-date-range">Use Date Range</label>
                                </div>
                            </div>
                            <div class="row g-3 mt-1 {{ $useDateRange ? 'd-none' : '' }}" id="single-date-wrapper">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Event Date & Time</label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control" 
                                        id="event-date-input"
                                        name="event_date" 
                                        value="{{ $singleDateValue }}" 
                                        {{ $useDateRange ? 'disabled' : 'required' }}
                                    >
                                    @error('event_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-1 {{ $useDateRange ? '' : 'd-none' }}" id="range-date-wrapper">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Starts</label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control range-date-input" 
                                        id="date-from-input"
                                        name="date_from" 
                                        value="{{ $rangeStartValue }}" 
                                        {{ $useDateRange ? 'required' : 'disabled' }}
                                    >
                                    @error('date_from')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ends</label>
                                    <input 
                                        type="datetime-local" 
                                        class="form-control range-date-input" 
                                        id="date-till-input"
                                        name="date_till" 
                                        value="{{ $rangeEndValue }}" 
                                        {{ $useDateRange ? 'required' : 'disabled' }}
                                    >
                                    @error('date_till')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Location</label>
                            <select name="location" class="form-select">
                                <option value="">Select location</option>
                                @foreach($locationOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('location', $selectedLocation ?? null) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('location')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Capacity</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="number" class="form-control" name="capacity" min="0" value="{{ old('capacity', $event->capacity ?? '') }}" placeholder="e.g. 60">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="capacity_unlimited" value="1" id="capacity-unlimited" {{ $unlimitedCapacity ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="capacity-unlimited">Unlimited</label>
                                </div>
                            </div>
                            @error('capacity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="3" required>{{ old('description', $event->description) }}</textarea>
                            @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" value="1" name="is_featured" id="is-featured" {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}>
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
                        <textarea name="about" class="form-control" rows="4">{{ old('about', $event->about) }}</textarea>
                        <div class="form-text">Separate bullet points with new lines or prefix them with "-" to render as lists.</div>
                        @error('about')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Event Highlights</label>
                        <textarea name="event_highlights" class="form-control" rows="4">{{ old('event_highlights', $event->event_highlights) }}</textarea>
                        <div class="form-text">Use new lines or leading "-" to break highlights into list items.</div>
                        @error('event_highlights')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">What to Expect</label>
                        <textarea name="what_to_expect" class="form-control" rows="4">{{ old('what_to_expect', $event->what_to_expect) }}</textarea>
                        <div class="form-text">Each line (or leading "-") becomes a bullet on the customer page.</div>
                        @error('what_to_expect')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('backoffice.events.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('use-date-range');
    const singleWrapper = document.getElementById('single-date-wrapper');
    const rangeWrapper = document.getElementById('range-date-wrapper');
    const singleInput = document.getElementById('event-date-input');
    const rangeInputs = document.querySelectorAll('.range-date-input');

    const syncScheduleInputs = () => {
        if (toggle.checked) {
            singleWrapper.classList.add('d-none');
            rangeWrapper.classList.remove('d-none');
            singleInput?.setAttribute('disabled', 'disabled');
            singleInput?.removeAttribute('required');
            rangeInputs.forEach((input) => {
                input.removeAttribute('disabled');
                input.setAttribute('required', 'required');
            });
        } else {
            singleWrapper.classList.remove('d-none');
            rangeWrapper.classList.add('d-none');
            singleInput?.removeAttribute('disabled');
            singleInput?.setAttribute('required', 'required');
            rangeInputs.forEach((input) => {
                input.setAttribute('disabled', 'disabled');
                input.removeAttribute('required');
            });
        }
    };

    toggle?.addEventListener('change', syncScheduleInputs);
    syncScheduleInputs();
});
</script>
</x-backoffice.layout>
