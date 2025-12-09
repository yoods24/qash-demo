<x-backoffice.layout>
    @php
        $attendance = $attendance ?? ['isPresent' => false, 'clockInTime' => null];
        $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
        $statusColor = $attendance['isPresent'] ? 'text-success' : 'text-danger';
        $statusLabel = $attendance['isPresent'] ? 'Present' : 'Not Clocked In';
        $statusNote = $attendance['isPresent']
            ? ($attendance['clockInTime'] ? 'Clocked in at ' . $attendance['clockInTime'] : 'Clocked in')
            : 'Please clock in to start your shift';
    @endphp

    <div class="mb-3">
        <div class="text-muted small">Welcome back</div>
        <h4 class="fw-bold mb-1">{{ $greeting ?? 'Hello' }}</h4>
        <div class="badge rounded-pill bg-light text-dark border">
            Role: {{ $roleName ?? 'Employee' }}
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Attendance Status</div>
                        <div class="fs-4 fw-bold {{ $statusColor }}">{{ $statusLabel }}</div>
                        <div class="small text-muted mt-1">{{ $statusNote }}</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-success bg-success-subtle">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Today's Sales Handled</div>
                        <div class="fs-4 fw-bold">{{ $money($salesHandled ?? 0) }}</div>
                        <div class="small text-muted mt-1">Based on served/completed orders today</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-primary bg-primary-subtle">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Orders Served Today</div>
                        <div class="fs-4 fw-bold">{{ number_format($ordersServed ?? 0) }} Orders</div>
                        <div class="small text-muted mt-1">Orders marked as served/completed today</div>
                    </div>
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-success bg-success-subtle">
                        <i class="bi bi-bag-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-info bg-info-subtle">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div>
                        <div class="text-muted small">You are signed in as</div>
                        <div class="fs-5 fw-bold">{{ $user?->fullName() ?? 'Team Member' }}</div>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark border me-1">{{ $roleName ?? 'Employee' }}</span>
                            <span class="badge {{ $attendance['isPresent'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                {{ $attendance['isPresent'] ? 'On Shift' : 'Off Shift' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-start gap-3">
                    <div class="metric-icon rounded d-flex align-items-center justify-content-center text-warning bg-warning-subtle">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-muted small">Quick Summary</div>
                        <div class="d-flex flex-wrap gap-4 mt-2">
                            <div>
                                <div class="text-muted small">Served Today</div>
                                <div class="fw-bold">{{ number_format($ordersServed ?? 0) }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Sales Today</div>
                                <div class="fw-bold">{{ $money($salesHandled ?? 0) }}</div>
                            </div>
                            <div>
                                <div class="text-muted small">Status</div>
                                <div class="fw-bold {{ $statusColor }}">{{ $statusLabel }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>
