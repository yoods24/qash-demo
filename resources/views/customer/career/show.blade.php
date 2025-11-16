<x-customer.layout>
  <section class="secondary-white">
    <div class="section-wrapper text-black" style="min-height: auto;">
      <div class="mb-4">
        <a href="{{ route('career.index', ['tenant' => request()->route('tenant')]) }}" class="text-decoration-none text-muted small">
          &larr; Back to open positions
        </a>
      </div>

      <div class="container px-0">
        <div class="career-card text-black bg-white rounded-4 shadow-sm p-4 p-md-5">
          <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
            <div class="flex-grow-1">
              <h3 class="mb-1">{{ $career->title }}</h3>
              <div class="d-flex flex-wrap gap-3 align-items-center small text-muted mb-2">
                <span><i class="bi bi-briefcase-fill primer me-1"></i> Cafe Operations</span>
                <span><i class="bi bi-geo-alt-fill primer me-1"></i> Downtown Location</span>
                <span><i class="bi bi-clock-fill primer me-1"></i> Full-time</span>
              </div>
              @if($career->salary_range)
                <div class="fw-semibold primer">
                  {{ $career->salary_range }} / month
                </div>
              @endif
            </div>
            <div class="mt-3 mt-md-0">
              <a href="mailto:hr@example.com?subject={{ urlencode('Application: ' . $career->title) }}"
                 class="btn btn-main px-4">
                Apply Now
              </a>
            </div>
          </div>

          @if($career->about)
            <p class="mb-4 text-secondary">
              {!! nl2br(e($career->about)) !!}
            </p>
          @endif

          @if($career->responsibilities)
            <div class="mb-4">
              <h5 class="mb-2">Key Responsibilities:</h5>
              <ul class="mb-0">
                @foreach(preg_split('/\r\n|\r|\n/', $career->responsibilities) as $line)
                  @if(trim($line) !== '')
                    <li>{{ $line }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif

          @if($career->requirements)
            <div>
              <h5 class="mb-2">Requirements:</h5>
              <ul class="mb-0">
                @foreach(preg_split('/\r\n|\r|\n/', $career->requirements) as $line)
                  @if(trim($line) !== '')
                    <li>{{ $line }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>
</x-customer.layout>

