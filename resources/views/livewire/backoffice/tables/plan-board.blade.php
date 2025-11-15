<div>
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-2">
        <div>
            <h4 class="mb-1">Table Plan</h4>
            <div class="text-muted small">Visualized by floors and status. Click an occupied table to transfer.</div>
        </div>
    </div>

    @if ($flashMessage)
        <div class="alert alert-{{ $flashType }} py-2" role="alert">{{ $flashMessage }}</div>
    @endif

    <!-- Transfer bar: stays while choosing destination -->
    @if ($fromTableId)
        <div class="alert alert-warning d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-semibold">Transfer in progress</div>
                <div class="small text-muted">
                    From <span class="fw-semibold">{{ $fromTableLabel }}</span>
                    <span class="mx-1">•</span>
                    Guest <span class="fw-semibold">{{ $guestName ?? '—' }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button wire:click="cancelTransfer" class="btn btn-sm btn-outline-secondary">Cancel</button>
            </div>
        </div>
    @endif

    <div class="mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @foreach($floors as $f)
                <button wire:click="switchFloor({{ $f->id }})"
                        class="btn btn-sm {{ $currentFloorId === $f->id ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ $f->name }}
                    @if($f->area_type)
                        <span class="badge bg-light text-dark ms-1">{{ $f->area_type }}</span>
                    @endif
                </button>
            @endforeach
            <a href="{{ route('backoffice.tables.index', ['tenant' => $tenantId, 'floor' => $currentFloorId]) }}" class="btn btn-sm btn-outline-secondary ms-auto">
                Manage Layout
            </a>
        </div>
    </div>

    <div class="card card-white p-2">
        <div class="grid-stack"
             id="tablesGrid-{{ $this->getId() }}"
             data-floor-id="{{ $currentFloorId }}">
            @foreach($tables as $t)
                @php
                    $borderColor = match($t->status) {
                        'available' => '#16a34a',
                        'occupied' => '#dc2626',
                        'oncleaning' => '#f59e0b',
                        'archived' => '#111827',
                        default => '#e2e8f0'
                    };
                    $guestName = optional($customerMap->get($t->id))->name;
                @endphp
                <div class="grid-stack-item" wire:key="table-{{ $t->id }}"
                    data-id="{{ $t->id }}"
                    data-label="{{ $t->label }}"
                    data-status="{{ $t->status }}"
                    data-shape="{{ $t->shape }}"
                    data-capacity="{{ $t->capacity }}"
                    data-color="{{ $t->color }}"
                    gs-x="{{ max(0, (int)($t->x ?? 0)) }}" gs-y="{{ max(0, (int)($t->y ?? 0)) }}" gs-w="{{ max(1, (int)($t->w ?: 2)) }}" gs-h="{{ max(1, (int)($t->h ?: 2)) }}">
                    <div class="grid-stack-item-content d-flex flex-column justify-content-center align-items-center"
                         style="background: {{ $t->color ?: '#f1f5f9' }}; border: 2px solid {{ $borderColor }}; border-radius: {{ $t->shape === 'circle' ? '50%' : '0.5rem' }}; {{ $t->shape === 'circle' ? 'aspect-ratio:1/1;' : '' }}">
                        <!-- Status icons (top-right) -->
                        <div class="position-absolute top-0 end-0 m-1 d-flex gap-1 flex-column">
                            <button type="button"
                                    wire:click="setStatus({{ $t->id }}, 'available')"
                                    @disabled($t->status === 'available')
                                    class="action-btn view-btn-table {{ $t->status === 'available' ? 'opacity-50' : '' }}"
                                    title="Mark Available">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button type="button"
                                    wire:click="setStatus({{ $t->id }}, 'oncleaning')"
                                    @disabled($t->status === 'oncleaning')
                                    class="action-btn yellow-btn-table {{ $t->status === 'available' ? 'opacity-50' : '' }}"
                                    title="Mark On Cleaning">
                                <i class="bi bi-brush"></i>
                            </button>
                            <button type="button"
                                    wire:click="setStatus({{ $t->id }}, 'occupied')"
                                    @disabled($t->status === 'occupied')
                                    class="action-btn delete-btn-table {{ $t->status === 'available' ? 'opacity-50' : '' }}"
                                    title="Mark Occupied">
                                <i class="bi bi-person-fill"></i>
                            </button>
                        </div>

                        <div class="fw-semibold">{{ $t->label }}</div>
                        <div class="small text-muted">{{ ucfirst($t->status) }} • {{ $t->capacity }} seats</div>
                        @if($guestName && $t->status === 'occupied')
                            <div class="small mt-1">Guest: {{ $guestName }}</div>
                        @endif
                        <div class="d-grid gap-2 mt-2 w-100" style="max-width: 140px;">
                            @if($t->status === 'occupied')
                                <button class="btn btn-sm btn-outline-primary" wire:click="startTransfer({{ $t->id }})">Transfer</button>
                            @endif
                            @if($t->status === 'available' && $fromTableId)
                                <button class="btn btn-sm btn-primary" wire:click="moveHere({{ $t->id }})">Move Here</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@script
<script>
    // Define the main GridStack function using Livewire's $js helper
    $js('planGrid', () => {
        const id = 'tablesGrid-{{ $this->getId() }}';
        const el = document.getElementById(id);
        
        if (typeof GridStack === 'undefined' || !el) {
            console.error('GridStack library or container element not found.');
            return; 
        }

        // Clean up any previous GridStack instance
        if (el.gridstack && typeof el.gridstack.destroy === 'function') {
             // Destroy without removing DOM elements (false)
            el.gridstack.destroy(false);
        }

        try {
            const grid = GridStack.init({
                staticGrid: true,
                column: 12,
                cellHeight: 80,
                margin: 10,
                oneColumnModeMaxWidth: 0,
                float: true,
                animate: true
            }, el);

            // Enforce DOM-provided positions once on first init, clamped to valid values
            const items = el.querySelectorAll('.grid-stack-item');
            if (items.length) {
                grid.batchUpdate();
                items.forEach((item) => {
                    const x = Math.max(0, parseInt(item.getAttribute('gs-x') || '0', 10));
                    const y = Math.max(0, parseInt(item.getAttribute('gs-y') || '0', 10));
                    const w = Math.max(1, parseInt(item.getAttribute('gs-w') || '2', 10));
                    const h = Math.max(1, parseInt(item.getAttribute('gs-h') || '2', 10));
                    grid.update(item, { x, y, w, h, autoPosition: false });
                });
                grid.commit();
            }
        } catch (e) { 
            console.error('GridStack init failed:', e); 
        }
    });

    // 1. Re-initialize after any Livewire property update (poll, button click)
    $wire.on('updated', () => { 
        $nextTick(() => $js.planGrid()); 
    });

    // 2. Initial call on component mount (uses $nextTick for post-DOM readiness)
    $nextTick(() => $js.planGrid());
    // 2b. Ensure layout after assets/fonts fully load
    window.addEventListener('load', () => $js.planGrid());
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => $js.planGrid());
    }
    
    // 3. Hook for Livewire Navigation (optional, but good for SPA behavior)
    document.addEventListener('livewire:navigated', () => $js.planGrid());

</script>
@endscript
