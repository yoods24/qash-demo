<x-backoffice.layout>
    <div class="d-flex align-items-center justify-content-between mb-3 mb-md-4 flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-cash text-orange fs-3 me-2"></i>
            <h4 class="mb-0">Sales Report</h4>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('backoffice.reports.sales', request()->route()->parameters()) }}" class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label">Start date</label>
                    <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" class="form-control" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">End date</label>
                    <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" class="form-control" />
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Granularity</label>
                    <select name="granularity" class="form-select">
                        @foreach(['daily' => 'Daily', 'monthly' => 'Monthly'] as $value => $label)
                            <option value="{{ $value }}" @selected($granularity === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        @foreach(['paid','pending','failed','cancelled'] as $ps)
                            <option value="{{ $ps }}" @selected(($paymentStatus ?? 'paid') === $ps)>{{ ucfirst($ps) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-sm-12 col-md-12 text-center">
                    <button class="btn btn-main w-100"><i class="bi bi-funnel me-1"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php
            $summaryCards = [
                ['label' => 'Gross Sales', 'value' => $totalSalesBeforeDiscount],
                ['label' => 'Discounts', 'value' => $totalDiscount],
                ['label' => 'Net Sales', 'value' => $netSales],
                ['label' => 'Tax', 'value' => $totalTax],
                ['label' => 'COGS', 'value' => $totalCogs],
                ['label' => 'Gross Profit', 'value' => $grossProfit],
            ];
        @endphp
        @foreach($summaryCards as $card)
            <div class="col-12 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <p class="text-primer mb-1 small text-uppercase">{{ $card['label'] }}</p>
                        <h5 class="mb-0 fw-bold">IDR {{ number_format((float) $card['value'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            @livewire('sales-chart-widget', [
                'startDate' => $startDate->toDateString(),
                'endDate' => $endDate->toDateString(),
                'granularity' => $granularity,
            ])
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-muted">Order</th>
                            <th class="text-muted">Date</th>
                            <th class="text-muted">Customer</th>
                            <th class="text-muted">Status</th>
                            <th class="text-muted">Net</th>
                            <th class="text-muted">Tax</th>
                            <th class="text-muted">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>#{{ $order->reference_no ?? $order->id }}</td>
                                <td>{{ optional($order->created_at)->format('M d, Y H:i') }}</td>
                                <td>{{ $order->customerDetail->name ?? 'Guest' }}</td>
                                <td><span class="badge bg-light text-dark text-uppercase">{{ str_replace('_',' ', $order->status) }}</span></td>
                                <td>IDR {{ number_format((float) (($order->grand_total ?? 0) - ($order->total_tax ?? 0)), 0, ',', '.') }}</td>
                                <td>IDR {{ number_format((float) $order->total_tax, 0, ',', '.') }}</td>
                                <td>IDR {{ number_format((float) $order->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No orders for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($orders instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="card-footer bg-white border-0">
                {{ $orders->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-backoffice.layout>
