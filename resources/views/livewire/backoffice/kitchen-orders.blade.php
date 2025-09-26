<div wire:poll.4s class="kitchen-board">
    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="kcol">
                <div class="kcol-header">
                    <div class="kcol-title"><i class="bi bi-clock-history me-2"></i> New Orders ({{ $newOrders->count() }})</div>
                </div>
                <div class="kcol-body">
                    @forelse($newOrders as $order)
                        <div class="kcard">
                            <div class="kcard-header">
                                <div class="left">
                                    <span class="order-id">Order #{{ $order->id }}</span>
                                </div>
                                <div class="right">{{ $order->created_at?->format('h:i A') }}</div>
                            </div>
                            <div class="kcard-body">
                                <div class="item-title mb-2">Items:</div>
                                @foreach($order->items as $it)
                                    @php
                                        $opt = $it->options['options'] ?? [];
                                        $notes = collect($opt)->pluck('value')->filter()->implode(', ');
                                    @endphp
                                    <div class="kitem">
                                        <div class="name">
                                            {{ $it->product_name }}
                                            @if($notes)
                                                <div class="note">Note: {{ $notes }}</div>
                                            @endif
                                        </div>
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                                <div class="kcard-actions">
                                    <button wire:click="startPreparing({{ $order->id }})" class="btn btn-sm btn-kitchen-primary">Start Preparing</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No new orders.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="kcol">
                <div class="kcol-header">
                    <div class="kcol-title"><i class="bi bi-check2-circle me-2"></i> In Progress ({{ $inProgress->count() }})</div>
                </div>
                <div class="kcol-body">
                    @forelse($inProgress as $order)
                        <div class="kcard">
                            <div class="kcard-header">
                                <div class="left">
                                    <span class="order-id">Order #{{ $order->id }}</span>
                                </div>
                                <div class="right">{{ $order->created_at?->format('h:i A') }}</div>
                            </div>
                            <div class="kcard-body">
                                <div class="item-title mb-2">Items:</div>
                                @foreach($order->items as $it)
                                    @php
                                        $opt = $it->options['options'] ?? [];
                                        $notes = collect($opt)->pluck('value')->filter()->implode(', ');
                                    @endphp
                                    <div class="kitem">
                                        <div class="name">
                                            {{ $it->product_name }}
                                            @if($notes)
                                                <div class="note">Note: {{ $notes }}</div>
                                            @endif
                                        </div>
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                                <div class="kcard-actions text-end">
                                    <button wire:click="markReady({{ $order->id }})" class="btn btn-sm btn-kitchen-secondary">Mark as Ready</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No orders in progress.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

