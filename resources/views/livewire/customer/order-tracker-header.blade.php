<div wire:poll.5s class="container my-5">
    @php
        $currency = $order->currency ?? 'IDR';
        $formatMoney = static fn ($value) => number_format((float) $value, 0, ',', '.');
        $statusLabel = ucfirst(str_replace('_', ' ', $activeStatus));
    @endphp

    <div class="d-flex flex-column align-items-center text-center mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 92px; height: 92px; background-color: #c6efce;">
            <i class="bi bi-check-circle-fill text-success fs-1"></i>
        </div>
        <h2 class="fw-bold text-black mb-2">Order Tracking</h2>
        <p class="mb-1 text-muted">
            Reference:
            <span class="fw-semibold text-uppercase text-primer">
                {{ $order->reference_no ? '#' . $order->reference_no : 'N/A' }}
            </span>
        </p>
        <p class="text-muted mb-3">Status refreshed every 5 seconds</p>
        <span class="badge rounded-pill px-4 py-2 bg-white text-black shadow-sm border" style="border-color: #e5e5e5;">
            Current status:
            <span class="fw-semibold text-capitalize">{{ $statusLabel }}</span>
        </span>
    </div>

    <div class="card shadow-sm rounded-4 p-4 border-0" style="border: 1px solid rgba(245, 130, 32, 0.2);">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
            @foreach ($steps as $index => $step)
                @php
                    $isActive = $step['state'] === 'active';
                    $isDone = $step['state'] === 'done';
                    $circleColor = $isActive
                        ? ($step['key'] === 'ready' ? '#c6efce' : '#f58220')
                        : ($isDone ? '#f58220' : '#e5e5e5');
                    $iconColor = $isActive || $isDone
                        ? ($step['key'] === 'ready' && $isActive ? '#198754' : '#ffffff')
                        : '#9ca3af';
                    $lineColor = $isDone ? '#f58220' : '#e5e5e5';
                @endphp
                <div class="d-flex flex-column align-items-center text-center flex-fill px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm"
                        style="width: 72px; height: 72px; background-color: {{ $circleColor }};">
                        <i class="bi {{ $step['icon'] }} fs-3" style="color: {{ $iconColor }};"></i>
                    </div>
                    <div class="fw-semibold text-capitalize">{{ $step['label'] }}</div>
                    <small class="text-muted">Step {{ $index + 1 }}</small>
                </div>
                @if (! $loop->last)
                    <div class="d-none d-md-flex align-items-center flex-grow-1">
                        <div style="height: 4px; width: 100%; background-color: {{ $lineColor }};"></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    @php
        $feesBreakdown = [
            [
                'label' => 'Order Total',
                'value' => $order->total,
                'highlight' => false,
            ],
            [
                'label' => 'Total Fees',
                'value' => $totalFees,
                'highlight' => false,
            ],
            [
                'label' => 'Net To Tenant',
                'value' => $netToTenant,
                'highlight' => true,
            ],
        ];
    @endphp

    <div class="row g-4 mt-4">
        @foreach ($feesBreakdown as $item)
            <div class="col-12 col-md-4">
                <div class="card shadow-sm rounded-4 border-0 h-100 text-center" style="border: 1px solid rgba(229, 229, 229, 0.9);">
                    <div class="card-body">
                        <small class="text-uppercase text-muted fw-semibold">{{ $item['label'] }}</small>
                        <p class="h5 fw-bold mb-0" style="color: {{ $item['highlight'] ? '#f58220' : '#111111' }};">
                            {{ $currency }} {{ $formatMoney($item['value']) }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
