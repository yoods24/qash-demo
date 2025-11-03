<x-backoffice.layout>
    <div class="d-flex align-items-center mb-3">
        <i class="bi bi-speedometer2 text-orange fs-3 me-2"></i>
        <h4 class="mb-0">Kitchen Performance</h4>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $from }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $to }}">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary">Apply</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Completed</div>
                <div class="fs-4 fw-bold">{{ $summary['totalCompleted'] }}</div>
            </div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">On Time</div>
                <div class="fs-4 fw-bold text-success">{{ $summary['onTime'] }}</div>
            </div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Late (Warning)</div>
                <div class="fs-4 fw-bold text-warning">{{ $summary['lateWarn'] }}</div>
            </div></div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Late (Danger)</div>
                <div class="fs-4 fw-bold text-danger">{{ $summary['lateDanger'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <div class="text-muted">Monthly Rating (avg of daily)</div>
                <div class="fs-3 fw-bold">{{ number_format($summary['monthlyRating'], 2) }} / 5.00</div>
            </div>
            <div class="fs-4">
                @php
                    $r = (float) $summary['monthlyRating'];
                    $full = floor($r);
                    $half = ($r - $full) >= 0.5 ? 1 : 0;
                    $empty = 5 - $full - $half;
                @endphp
                @for($i=0;$i<$full;$i++) <i class="bi bi-star-fill text-warning"></i> @endfor
                @if($half) <i class="bi bi-star-half text-warning"></i> @endif
                @for($i=0;$i<$empty;$i++) <i class="bi bi-star text-warning"></i> @endfor
            </div>
        </div>
    </div>

    

    <div class="card shadow-sm">
        <div class="card-header fw-semibold">Daily Ratings</div>
        <div class="card-body">
            @if(empty($dailyRatings))
                <div class="text-muted">No data.</div>
            @else
                <ul class="mb-0">
                @foreach($dailyRatings as $d)
                    <li>{{ $d['day'] }} — {{ number_format($d['score'], 2) }}/5</li>
                @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-backoffice.layout>
