<x-backoffice.layout>
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-cash text-orange fs-3 me-2"></i>
            <h4 class="mb-0">Sales Report</h4>
        </div>
        <div>
            <a href="{{ route('backoffice.reports.sales', array_merge(request()->route()->parameters(), request()->query(), ['export' => 1])) }}" class="btn btn-sm btn-warning">
                <i class="bi bi-box-arrow-down me-1"></i> Export
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('backoffice.reports.sales', request()->route()->parameters()) }}" class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control" />
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Any</option>
                        @foreach(['confirmed','preparing','ready'] as $s)
                            <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Any</option>
                        @foreach(['paid','unpaid','cancelled'] as $p)
                            <option value="{{ $p }}" @selected(($payment_status ?? null) === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-1 text-end">
                    <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted">Date</th>
                            <th class="text-muted">Total Orders</th>
                            <th class="text-muted">Total Products</th>
                            <th class="text-muted">Subtotal</th>
                            <th class="text-muted">Tax</th>
                            <th class="text-muted">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr>
                                <td>{{ $r->day }}</td>
                                <td>{{ (int) $r->total_orders }}</td>
                                <td>{{ (int) $r->total_products }}</td>
                                <td>{{ number_format((float) $r->subtotal, 2) }}</td>
                                <td>{{ number_format((float) $r->tax, 2) }}</td>
                                <td>{{ number_format((float) $r->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No data for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-backoffice.layout>

