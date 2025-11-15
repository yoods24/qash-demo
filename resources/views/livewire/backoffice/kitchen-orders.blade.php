<div wire:poll.4s class="kitchen-board">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <h4 class="mb-0">Kitchen Orders</h4>
        <div class="ko-search" style="min-width:260px;">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input wire:model.debounce.400ms="search" type="text" class="form-control" placeholder="Search Order" />
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Items Board (col-3) -->
        <div class="col-12 col-md-3">
            <div class="ko-card h-100">
                <div class="ko-card-top d-flex align-items-center justify-content-between p-3">
                    <h6 class="mb-0">Items Board</h6>
                    <span class="text-muted small">{{ collect($itemsBoard ?? [])->sum('qty') }} items</span>
                </div>
                <div class="ko-card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse(($itemsBoard ?? []) as $ib)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $ib['name'] }}</div>
                                    @if(!empty($ib['options']))
                                        <div class="text-muted small text-truncate">{{ $ib['options'] }}</div>
                                    @endif
                                </div>
                                <span class="badge bg-primary rounded-pill">x{{ $ib['qty'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted small">No active items.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dine-in container (Confirmed + Preparing, takes rest) -->
        <div class="col-12 col-md-9 dine-in-container">
            @if(isset($todayStats))
            <div class=" mb-3">
                <div class="">
                    <div class="d-flex flex-wrap gap-2 kds-overview">
                        <div class="kv green-strong"><div class="num">{{ $todayStats['totalCompleted'] }}</div><div class="label">Completed</div></div>
                        <div class="kv green"><div class="num">{{ $todayStats['onTime'] }}</div><div class="label">On Time</div></div>
                        <div class="kv yellow"><div class="num">{{ $todayStats['lateWarn'] }}</div><div class="label">Late (Warn)</div></div>
                        <div class="kv red"><div class="num">{{ $todayStats['lateDanger'] }}</div><div class="label">Late (Danger)</div></div>
                        <div class="kv blue"><div class="num">{{ $counts['confirmed'] ?? 0 }}</div><div class="label">Queue</div></div>
                        <div class="kv blue"><div class="num">{{ $counts['preparing'] ?? 0 }}</div><div class="label">Preparing</div></div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Confirmed Orders (Queue) -->
            <h6 class="mb-2">Confirmed</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-4">
                @forelse(($confirmedOrders ?? []) as $order)
                    @php
                        $stage = 'confirmed';
                        $startTs = $order->confirmed_at ?? $order->created_at;
                        $expected = (int) ($order->expected_seconds_total ?? 0);
                        $warnAt = 300;
                        $dangerAt = 600;
                        $collapseId = 'ko-items-' . $order->id;
                    @endphp
                    <div class="col" wire:key="order-confirmed-{{ $order->id }}">
                        <div class="ko-card ko-card-dinein h-100">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-primary"></i>
                                    <span class="ko-order-link">#{{ str_pad((string)$order->id, 7, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="kds-timer badge bg-light text-dark kds-time-ok" data-role="kds-timer" data-stage="{{ $stage }}" data-start="{{ optional($startTs)->toIso8601String() }}" data-start-epoch="{{ optional($startTs)->getTimestamp() ?? 0 }}" data-expected="{{ $expected }}" data-warn="{{ $warnAt }}" data-danger="{{ $dangerAt }}">--:--</span>
                                </div>
                            </div>
                            <div class="ko-card-body p-3">
                                <div class="text-muted small">Table No: <span class="fw-semibold">{{ $order->customerDetail?->diningTable?->label ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">Customer Name: <span class="fw-semibold">{{ $order->customerDetail?->name ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">Customer Email: <span class="fw-semibold">{{ $order->customerDetail?->email ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">{{ $order->created_at?->format('h:i A, d-m-Y') }}</div>
                                <hr class="my-2">
                                @foreach($order->items as $it)
                                    @php
                                        $raw = is_array($it->options) ? $it->options : [];
                                        $raw = (is_array($raw) && array_key_exists('options', $raw)) ? ($raw['options'] ?? []) : $raw;
                                        $pairs = [];
                                        foreach ($raw as $optId => $data) {
                                            $optModel = optional(optional($it->product)->options)->firstWhere('id', (int) $optId);
                                            $optName = $optModel->name ?? 'Option';
                                            $val = is_array($data) ? ($data['value'] ?? '') : (string) $data;
                                            if ($val !== '') $pairs[] = $optName . ': ' . $val;
                                        }
                                        $optionsText = implode(', ', $pairs);
                                    @endphp
                                    <div class="kitem border-0 py-1">
                                        <div class="min-w-0">
                                            <div class="name">{{ $it->product_name }}</div>
                                            @if($optionsText)
                                                <div class="text-muted small">{{ $optionsText }}</div>
                                            @endif
                                        </div>
                                        @if(!empty($it->special_instructions))
                                            <div class="text-danger small mt-1">Note: {{ $it->special_instructions }}</div>
                                        @endif
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                            <div class="d-flex justify-content-end p-3">
                                    @can('kitchen_kds_update_order')
                                        <button type="button" wire:click="startPreparing({{ $order->id }})" class="btn btn-sm btn-kitchen-preparing">Start Preparing</button>
                                    @endcan
                            </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No confirmed orders.</div>
                @endforelse
            </div>

            <hr>

            <!-- Preparing Orders -->
            <h6 class="mb-2">Preparing</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-4">
                @forelse(($preparingOrders ?? []) as $order)
                    @php
                        $stage = 'preparing';
                        // Measure elapsed from confirmed/created to align with expected_seconds_total SLA
                        $startTs = $order->confirmed_at ?? $order->created_at;
                        $expected = (int) ($order->expected_seconds_total ?? 0);
                        $warnAt = $expected + 300;   // +5 minutes grace
                        $dangerAt = $expected + 600; // +10 minutes grace
                        $collapseId = 'ko-items-' . $order->id;
                    @endphp
                    <div class="col" wire:key="order-preparing-{{ $order->id }}">
                        <div class="ko-card ko-card-dinein h-100">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-primary"></i>
                                    <span class="ko-order-link">#{{ str_pad((string)$order->id, 7, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="kds-timer badge bg-light text-dark kds-time-ok" data-role="kds-timer" data-stage="{{ $stage }}" data-start="{{ optional($startTs)->toIso8601String() }}" data-start-epoch="{{ optional($startTs)->getTimestamp() ?? 0 }}" data-expected="{{ $expected }}" data-warn="{{ $warnAt }}" data-danger="{{ $dangerAt }}">--:--</span>
                                </div>
                            </div>
                            <div class="ko-card-body p-3">
                                <div class="text-muted small">Table No: <span class="fw-semibold">{{ $order->customerDetail?->diningTable?->label ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">Customer Name: <span class="fw-semibold">{{ $order->customerDetail?->name ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">Customer Email: <span class="fw-semibold">{{ $order->customerDetail?->email ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">{{ $order->created_at?->format('h:i A, d-m-Y') }}</div>
                                <hr class="my-2">
                                @foreach($order->items as $it)
                                    @php
                                        $raw = is_array($it->options) ? $it->options : [];
                                        $raw = (is_array($raw) && array_key_exists('options', $raw)) ? ($raw['options'] ?? []) : $raw;
                                        $pairs = [];
                                        foreach ($raw as $optId => $data) {
                                            $optModel = optional(optional($it->product)->options)->firstWhere('id', (int) $optId);
                                            $optName = $optModel->name ?? 'Option';
                                            $val = is_array($data) ? ($data['value'] ?? '') : (string) $data;
                                            if ($val !== '') $pairs[] = $optName . ': ' . $val;
                                        }
                                        $optionsText = implode(', ', $pairs);
                                    @endphp
                                    <div class="kitem border-0 py-1">
                                        <div class="min-w-0">
                                            <div class="name">{{ $it->product_name }}</div>
                                            @if($optionsText)
                                                <div class="text-muted small">{{ $optionsText }}</div>
                                            @endif
                                        </div>
                                        @if(!empty($it->special_instructions))
                                            <div class="text-danger small mt-1">Note: {{ $it->special_instructions }}</div>
                                        @endif
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-end">
                                    @can('kitchen_kds_confirm_order')
                                        <button type="button" wire:click="markReady({{ $order->id }})" class="btn btn-sm btn-kitchen-ready">Mark as Ready</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No preparing orders.</div>
                @endforelse
            </div>

            <hr>

            <!-- Done Orders -->
            <h6 class="mb-2">Done</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-4">
                @forelse(($doneOrders ?? []) as $order)
                    @php
                        // Prefer stored total_seconds; fallback to computed accessor
                        $totalSecs = (int) ($order->total_seconds ?? 0);
                        if ($totalSecs <= 0) {
                            $totalSecs = (int) ($order->computed_total_seconds ?? 0);
                        }
                        $duration = gmdate('H:i:s', max(0, $totalSecs));
                    @endphp
                    <div class="col" wire:key="order-done-{{ $order->id }}">
                        <div class="ko-card ko-card-dinein h-100">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-success"></i>
                                    <span class="ko-order-link">#{{ str_pad((string)$order->id, 7, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="badge bg-success-subtle text-success">Total {{ $duration }}</span>
                                </div>
                            </div>
                            <div class="ko-card-body p-3">
                                <div class="text-muted small">Table No: <span class="fw-semibold">{{ $order->customerDetail?->diningTable?->label ?? 'Unassigned' }}</span></div>
                                <div class="text-muted small">{{ $order->ready_at?->format('h:i A, d-m-Y') }}</div>
                                <hr class="my-2">
                                @foreach($order->items as $it)
                                    @php
                                        $raw = is_array($it->options) ? $it->options : [];
                                        $raw = (is_array($raw) && array_key_exists('options', $raw)) ? ($raw['options'] ?? []) : $raw;
                                        $pairs = [];
                                        foreach ($raw as $optId => $data) {
                                            $optModel = optional(optional($it->product)->options)->firstWhere('id', (int) $optId);
                                            $optName = $optModel->name ?? 'Option';
                                            $val = is_array($data) ? ($data['value'] ?? '') : (string) $data;
                                            if ($val !== '') $pairs[] = $optName . ': ' . $val;
                                        }
                                        $optionsText = implode(', ', $pairs);
                                    @endphp
                                    <div class="kitem border-0 py-1">
                                        <div class="min-w-0">
                                            <div class="name">{{ $it->product_name }}</div>
                                            @if($optionsText)
                                                <div class="text-muted small">{{ $optionsText }}</div>
                                            @endif
                                        </div>
                                        @if(!empty($it->special_instructions))
                                            <div class="text-danger small mt-1">Note: {{ $it->special_instructions }}</div>
                                        @endif
                                        <div class="qty">x{{ $it->quantity }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No done orders.</div>
                @endforelse
            </div>

            <!-- Takeaway (mock) at bottom -->
            <h6 class="mb-2">Takeaway</h6>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                @foreach(($takeawayOrders ?? collect()) as $tk)
                    @php
                        $statusLabel = $tk['status'] === 'ready' ? 'Done' : ucfirst($tk['status']);
                        $statusType = $tk['status'] === 'ready' ? 'success' : ($tk['status'] === 'preparing' ? 'warning' : 'info');
                    @endphp
                    <div class="col">
                        <div class="ko-card ko-card-takeaway h-100">
                            <div class="ko-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bag-fill text-purple"></i>
                                    <span class="ko-order-link text-purple">#{{ $tk['id'] }}</span>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="ko-chip ko-chip-{{ $statusType }}">{{ $statusLabel }}</span>
                                </div>
                            </div>
                            <div class="ko-card-body p-3">
                                <div class="text-muted small">Token No: <span class="fw-semibold">{{ $tk['token'] }}</span></div>
                                <div class="text-muted small">{{ $tk['time'] }}</div>
                                <hr class="my-2">
                                @foreach($tk['items'] as $it)
                                    <div class="kitem border-0 py-1">
                                        <div class="name">{{ $it['name'] }}</div>
                                        <div class="qty">x{{ $it['qty'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
