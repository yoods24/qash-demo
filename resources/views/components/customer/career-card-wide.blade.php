@props(['career'])

@php
    $aboutPreview = \Illuminate\Support\Str::limit(strip_tags($career->about), 180);
@endphp

<div class="col-12 mb-4">
  <div class="career-card text-black bg-white rounded-4 shadow-sm p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
      <div class="flex-grow-1">
        <h4 class="mb-1">{{ $career->title }}</h4>
        <div class="d-flex flex-wrap gap-3 align-items-center small text-muted mb-2">
          <span><i class="bi bi-briefcase-fill primer me-1"></i> Cafe Operations</span>
          <span><i class="bi bi-geo-alt-fill primer me-1"></i> Downtown Location</span>
          <span><i class="bi bi-clock-fill primer me-1"></i> Full-time</span>
        </div>
        @if($career->salary_range)
          <div class="fw-semibold primer mb-2">{{ $career->salary_range }} / month</div>
        @endif
        <p class="mb-0 text-secondary">{{ $aboutPreview }}</p>
      </div>
      <div class="mt-3 mt-md-0">
        <a href="{{ route('customer.career.show', ['tenant' => request()->route('tenant'), 'career' => $career]) }}" class="btn btn-main px-4">
          Apply Now
        </a>
      </div>
    </div>
  </div>
</div>
