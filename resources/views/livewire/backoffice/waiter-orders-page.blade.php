
<div wire:poll.5s="refreshOrders" class="waiter-orders-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0">Waiter Orders</h4>
            <span class="badge bg-success-subtle text-success">Live</span>
        </div>
        <div style="min-width: 260px;" class="ms-auto">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" placeholder="Search order / customer" wire:model.debounce.500ms="orderSearch">
            </div>
        </div>
    </div>

    @php
        $statusStyles = [
            'ready' => ['label' => 'Ready', 'class' => 'badge bg-success-subtle text-success'],
            'preparing' => ['label' => 'Preparing', 'class' => 'badge bg-warning-subtle text-warning'],
            'confirmed' => ['label' => 'Queue', 'class' => 'badge bg-primary-subtle text-primary'],
        ];
        $overviewOrders = collect($readyOrders)->concat($inProgressOrders)->sortByDesc('created_at_ts');
        $activeOrdersCount = count($readyOrders) + count($inProgressOrders);
    @endphp

    <div class="row g-3">
        <div class="col-12 col-xl-3">
            <div class="ko-card h-100">
                <div class="ko-card-top d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">Orders Overview</div>
                        <div class="text-muted small">{{ $activeOrdersCount }} active orders</div>
                    </div>
                </div>
                <div class="ko-card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($overviewOrders as $order)
                            @php
                                $badge = $statusStyles[$order['status']] ?? ['label' => ucfirst($order['status']), 'class' => 'badge bg-secondary-subtle text-secondary'];
                            @endphp
                            <a href="#order-{{ $order['id'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" wire:key="overview-order-{{ $order['id'] }}">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-primary">#{{ $order['reference'] }}</span>
                                    <span class="small text-muted">{{ $order['order_type'] }}</span>
                                </div>
                                <span class="{{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </a>
                        @empty
                            <div class="p-3 text-muted small">No active orders.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-9">
            <div class="d-flex flex-wrap align-items-stretch gap-3 mb-3">
                <div class="flex-grow-1">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-success-subtle h-100">
                                <div class="text-muted small">Ready to Serve</div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="display-6 fw-bold text-success">{{ $stats['ready'] ?? 0 }}</div>
                                    <i class="bi bi-check2-circle text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-warning-subtle h-100">
                                <div class="text-muted small">Preparing</div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="display-6 fw-bold text-warning">{{ $stats['preparing'] ?? 0 }}</div>
                                    <i class="bi bi-clock-history text-warning fs-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-primary-subtle h-100">
                                <div class="text-muted small">Queue</div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="display-6 fw-bold text-primary">{{ $stats['queue'] ?? 0 }}</div>
                                    <i class="bi bi-list-ul text-primary fs-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-light h-100">
                                <div class="text-muted small">Served Today</div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <div class="display-6 fw-bold">{{ $stats['served'] ?? 0 }}</div>
                                    <i class="bi bi-check-all text-secondary fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">Ready to Serve</h5>
                        <span class="badge rounded-pill bg-success-subtle text-success fw-semibold">{{ count($readyOrders) }}</span>
                    </div>

                    @forelse($readyOrders as $order)
                        @php
                            $itemsPreview = array_slice($order['items'], 0, 2);
                            $remaining = max(0, ($order['items_line_count'] ?? count($order['items'])) - count($itemsPreview));
                            $noteLine = collect($order['items'] ?? [])->first(fn ($it) => !empty($it['note'] ?? ''));
                        @endphp
                        <div class="ko-card order-card ready mb-3" id="order-{{ $order['id'] }}" wire:key="ready-order-{{ $order['id'] }}">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ko-order-link">#{{ $order['reference'] }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="badge bg-warning text-dark">{{ $order['order_type'] }}</span>
                                    <span class="badge bg-light text-danger d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-clock-history"></i>
                                        {{ $order['elapsed_time'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="DD-card-body">
                                <div class="text-muted small mb-1">Order Type: <span class="fw-semibold">{{ $order['order_mode'] }}{{ $order['table_label'] ? ' (Table '.$order['table_label'].')' : '' }}</span></div>
                                <div class="text-muted small mb-1">Customer Name: <span class="fw-semibold">{{ $order['customer_name'] ?? 'Walk-in' }}</span></div>
                                <div class="text-muted small mb-3">Customer Email: <span class="fw-semibold">{{ $order['customer_email'] ?? '—' }}</span></div>

                                <div class="mt-2">
                                    @foreach($itemsPreview as $idx => $item)
                                        <div class="d-flex justify-content-between align-items-center py-1" wire:key="ready-item-{{ $order['id'] }}-{{ $idx }}">
                                            <div class="fw-semibold text-truncate">{{ $item['product_name'] }}</div>
                                            <span class="text-muted">x{{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                    @if($remaining > 0)
                                        <div class="text-muted small">+{{ $remaining }} more</div>
                                    @endif
                                </div>

                                @if($noteLine)
                                    <div class="rounded-3 p-2 mt-3 d-flex align-items-center gap-2 bg-warning-subtle border border-warning-subtle">
                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                        <span class="small">{{ $noteLine['note'] }}</span>
                                    </div>
                                @else
                                    <div class="rounded-3 p-2 mt-3 d-flex align-items-center gap-2 bg-light border">
                                        <i class="bi bi-journal-minus text-muted"></i>
                                        <span class="small text-muted">No instructions</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 d-flex gap-2 border-top">
                                <button type="button" class="btn btn-primer w-100" wire:click="markAsServed({{ $order['id'] }})">Mark as Served</button>
                                <button type="button" class="btn btn-outline-secondary">Cannot Serve</button>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border">No ready orders right now.</div>
                    @endforelse
                </div>

                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 class="mb-0">In Progress</h5>
                        <span class="badge rounded-pill bg-info-subtle text-primary fw-semibold">{{ count($inProgressOrders) }}</span>
                    </div>

                    @forelse($inProgressOrders as $order)
                        @php
                            $itemsPreview = array_slice($order['items'], 0, 2);
                            $remaining = max(0, ($order['items_line_count'] ?? count($order['items'])) - count($itemsPreview));
                            $noteLine = collect($order['items'] ?? [])->first(fn ($it) => !empty($it['note'] ?? ''));
                            $badge = $statusStyles[$order['status']] ?? ['label' => ucfirst($order['status']), 'class' => 'badge bg-secondary-subtle text-secondary'];
                        @endphp
                        <div class="ko-card order-card in-progress mb-3" id="order-{{ $order['id'] }}" wire:key="progress-order-{{ $order['id'] }}">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="ko-order-link">#{{ $order['reference'] }}</span>
                                    <span class="{{ $badge['class'] }}">{{ $badge['label'] }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="badge bg-warning text-dark">{{ $order['order_type'] }}</span>
                                    <span class="badge bg-light text-danger d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-clock-history"></i>
                                        {{ $order['elapsed_time'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="DD-card-body">
                                <div class="text-muted small mb-1">Order Type: <span class="fw-semibold">{{ $order['order_mode'] }}{{ $order['table_label'] ? ' (Table '.$order['table_label'].')' : '' }}</span></div>
                                <div class="text-muted small mb-1">Customer Name: <span class="fw-semibold">{{ $order['customer_name'] ?? 'Walk-in' }}</span></div>
                                <div class="text-muted small mb-3">Customer Email: <span class="fw-semibold">{{ $order['customer_email'] ?? '—' }}</span></div>

                                <div class="mt-2">
                                    @foreach($itemsPreview as $idx => $item)
                                        <div class="d-flex justify-content-between align-items-center py-1" wire:key="progress-item-{{ $order['id'] }}-{{ $idx }}">
                                            <div class="fw-semibold text-truncate">{{ $item['product_name'] }}</div>
                                            <span class="text-muted">x{{ $item['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                    @if($remaining > 0)
                                        <div class="text-muted small">+{{ $remaining }} more</div>
                                    @endif
                                </div>

                                @if($noteLine)
                                    <div class="rounded-3 p-2 mt-3 d-flex align-items-center gap-2 bg-warning-subtle border border-warning-subtle">
                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                        <span class="small">{{ $noteLine['note'] }}</span>
                                    </div>
                                @else
                                    <div class="rounded-3 p-2 mt-3 d-flex align-items-center gap-2 bg-light border">
                                        <i class="bi bi-journal-minus text-muted"></i>
                                        <span class="small text-muted">No instructions</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 border-top">
                                <button type="button" class="btn btn-outline-primary w-100" wire:click="$dispatch('pos-notify-customer', { id: {{ $order['id'] }} })">
                                    <i class="bi bi-bell me-1"></i> Notify Customer
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border">No orders in progress.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
