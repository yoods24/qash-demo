<div wire:poll.4s class="kitchen-board">
    <!-- Toolbar: Tabs + Search -->
    <div class="mb-3 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-2 ko-toolbar">
        <div class="d-flex gap-2">
            @php
                $tabs = [
                    'all' => 'All Orders',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'Preparing',
                    'done' => 'Done',
                ];
            @endphp
            @foreach($tabs as $key => $label)
                <button type="button" wire:click="setStatus('{{ $key }}')" class="ko-tab {{ ($status ?? 'all') === $key ? 'active' : '' }}">
                    {{ $label }}
                    @if(isset($counts[$key]))<span class="ms-1 badge bg-light text-dark">{{ $counts[$key] }}</span>@endif
                </button>
            @endforeach
        </div>
        <div class="ko-search ms-md-3">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input wire:model.debounce.400ms="search" type="text" class="form-control" placeholder="Search Order" />
            </div>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <!-- Items Board -->
        <div class="col-12 col-lg-3">
            <div class="kcol h-100">
                <div class="kcol-header border-bottom pb-2 mb-2">
                    <div class="kcol-title">Items Board</div>
                </div>
                <div class="kcol-body p-0">
                    @forelse(($itemsBoard ?? []) as $item)
                        <div class="ko-item-row">
                            <div class="ko-item-title">{{ $item['name'] }}</div>
                            <div class="ko-item-qty">{{ $item['qty'] }}</div>
                        </div>
                    @empty
                        <div class="text-muted p-3">No items to show.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Dine-In Orders -->
        <div class="col-12 col-lg-6">
            <div class="kcol h-100">
                <div class="kcol-header border-bottom pb-2 mb-2">
                    <div class="kcol-title">Dine-In Orders</div>
                </div>
                <div class="kcol-body">
                    @forelse(($dineInOrders ?? []) as $order)
                        @php
                            $statusLabel = match($order->status) {
                                'preparing' => 'Preparing',
                                'ready' => 'Done',
                                default => 'Confirmed',
                            };
                            $statusType = match($order->status) {
                                'preparing' => 'warning',
                                'ready' => 'success',
                                default => 'info',
                            };
                            $collapseId = 'ko-items-' . $order->id;
                        @endphp
                        <div class="ko-card ko-card-dinein">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-primary"></i>
                                    <a href="javascript:void(0)" class="ko-order-link">#{{ str_pad((string)$order->id, 7, '0', STR_PAD_LEFT) }}</a>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="ko-chip ko-chip-{{ $statusType }}">{{ $statusLabel }}</span>
                                    <button class="btn btn-sm btn-light border-0 text-primary ko-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ko-card-body">
                                <div class="text-muted small">Table No: <span class="fw-semibold">Table 1</span></div>
                                <div class="text-muted small">Token No: <span class="fw-semibold">{{ str_pad((string)($order->id % 200 + 100), 4, '0', STR_PAD_LEFT) }}</span></div>
                                <div class="text-muted small">{{ $order->created_at?->format('h:i A, d-m-Y') }}</div>
                            </div>
                            <div id="{{ $collapseId }}" class="collapse ko-items px-3 pb-3">
                                @foreach($order->items as $it)
                                    <div class="kitem">
                                        <div class="name">{{ $it->product_name }}</div>
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                                <div class="text-end mt-2">
                                    @if($order->status === 'pending')
                                        <button wire:click="startPreparing({{ $order->id }})" class="btn btn-sm btn-kitchen-primary">Start Preparing</button>
                                    @elseif($order->status === 'preparing')
                                        <button wire:click="markReady({{ $order->id }})" class="btn btn-sm btn-kitchen-secondary">Mark as Ready</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No orders found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Takeaway (mock) -->
        <div class="col-12 col-lg-3">
            <div class="kcol h-100">
                <div class="kcol-header border-bottom pb-2 mb-2">
                    <div class="kcol-title">Takeaway</div>
                </div>
                <div class="kcol-body">
                    @foreach(($takeawayOrders ?? collect()) as $tk)
                        @php
                            $statusLabel = $tk['status'] === 'ready' ? 'Done' : ucfirst($tk['status']);
                            $statusType = $tk['status'] === 'ready' ? 'success' : ($tk['status'] === 'preparing' ? 'warning' : 'info');
                            $cid = 'ko-tk-' . $tk['id'];
                        @endphp
                        <div class="ko-card ko-card-takeaway">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-purple"></i>
                                    <a href="javascript:void(0)" class="ko-order-link text-purple">#{{ $tk['id'] }}</a>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="ko-chip ko-chip-{{ $statusType }}">{{ $statusLabel }}</span>
                                    <button class="btn btn-sm btn-light border-0 text-primary ko-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $cid }}" aria-expanded="false" aria-controls="{{ $cid }}">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ko-card-body">
                                <div class="text-muted small">Token No: <span class="fw-semibold">{{ $tk['token'] }}</span></div>
                                <div class="text-muted small">{{ $tk['time'] }}</div>
                            </div>
                            <div id="{{ $cid }}" class="collapse ko-items px-3 pb-3">
                                @foreach($tk['items'] as $it)
                                    <div class="kitem">
                                        <div class="name">{{ $it['name'] }}</div>
                                        <div class="qty">x{{ $it['qty'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
