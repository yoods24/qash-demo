<div class="pos-page">
    <div class="container-fluid bg-main">
        <div class="row g-3">
            <!-- Products grid (scrollable area) -->
            <div class="col-md-7 col-lg-8 pos-left-column d-flex flex-column">
                <div class="pos-products-scroll" data-lenis-prevent>
                    <!-- Search + Categories now scroll with products -->
                    <div class="">
                        <div class="row g-3 align-items-center mb-2">
                            <div class="col-12">
                                <div class="input-group pos-search shadow-sm">
                                    <input type="text" class="form-control pos-search-input" placeholder="Search by Menu Item" wire:model.debounce.300ms="search">
                                    <button class="btn btn-main pos-search-btn" type="button" aria-label="Search">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Category Splide slider -->
                        <div id="pos-cat-slider" class="splide" wire:ignore>
                            <div class="splide__track">
                                <ul class="splide__list">
                                    <li class="splide__slide py-2">
                                        <button type="button" class="pos-cat-btn {{ $categoryId ? '' : 'active' }}" data-cat-id="all" wire:click="selectCategory(null)">
                                            <span class="pos-cat-label">All Items</span>
                                        </button>
                                    </li>
                                    @foreach($this->categories as $cat)
                                        <li class="splide__slide py-2" wire:key="cat-{{ $cat->id }}">
                                            <button type="button" class="pos-cat-btn {{ $categoryId === $cat->id ? 'active' : '' }}" data-cat-id="{{ $cat->id }}" wire:click="selectCategory({{ $cat->id }})">
                                                <span class="pos-cat-label">{{ $cat->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-3 mt-3">
                        @forelse($this->products as $product)
                            @php
                                $stockQty = (int) ($product->stock_qty ?? 0);
                                $isOutOfStock = $stockQty <= 0;
                            @endphp
                            <div class="col" wire:key="prod-{{ $product->id }}">
                                <div class="card product-card h-100 shadow-sm {{ $isOutOfStock ? 'product-card-disabled' : '' }}">
                                    <div class="ratio ratio-4x3">
                                        @php $img = $product->product_image_url ?? null; @endphp
                                        @if($img)
                                            <img src="{{ $img }}" alt="{{ $product->name }}" class="card-img-top object-fit-cover">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center bg-light text-muted">No Image</div>
                                        @endif
                                        @if($isOutOfStock)
                                            <span class="pos-stock-badge">Out of Stock</span>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <div class="fw-semibold text-truncate" title="{{ $product->name }}">{{ $product->name }}</div>
                                        <div class="text-muted small mb-2">
                                            Rp.{{ number_format((float)($product->price ?? 0), 2) }}
                                            @if($product->stock_qty !== null)
                                                <span class="text-muted small d-block">Stock: {{ max(0, $stockQty) }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-auto text-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm {{ $isOutOfStock ? 'btn-outline-secondary disabled' : 'btn-outline-primary' }}"
                                                @unless($isOutOfStock) wire:click="showProductOptions({{ $product->id }})" @endunless
                                                @if($isOutOfStock) disabled aria-disabled="true" @endif
                                            >
                                                @if($isOutOfStock)
                                                    <i class="bi bi-exclamation-triangle"></i> Out of Stock
                                                @else
                                                    <i class="bi bi-bag-plus"></i> Add
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-secondary">No products found.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right pane: customer + order type + table + bill summary -->
            <div class="col-md-5 col-lg-4 pos-right-column">
                <div class="card h-100">
                    <div class="card-body" data-lenis-prevent>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label mb-0">Customer</label>
                </div>
                <div class="input-group mb-3">
                    <select class="form-select" wire:model="customerId">
                        <option value="">Walking Customer</option>
                        @foreach($this->customers as $cust)
                            <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-sm btn-main" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <i class="bi bi-plus-circle"></i> Add
                    </button>
                </div>

                <div class="mb-2"><label class="form-label">Select Order Type</label></div>
                <div class="btn-group mb-3 w-100" role="group">
                    <input type="radio" class="btn-check" name="orderType" id="ot-dine" value="dine-in" autocomplete="off" wire:model="orderType">
                    <label class="btn btn-outline-warning" for="ot-dine"><i class="bi bi-shop"></i> Dine-In</label>

                    <input type="radio" class="btn-check" name="orderType" id="ot-take" value="takeaway" autocomplete="off" wire:model="orderType">
                    <label class="btn btn-outline-warning" for="ot-take"><i class="bi bi-bag"></i> Takeaway</label>
                </div>

                @if($orderType === 'dine-in')
                    <div class="mb-3">
                        <label class="form-label">Select Table</label>
                        <select class="form-select" wire:model="tableId">
                            <option value="">Select Table</option>
                            @foreach($this->tables as $t)
                                @php
                                    $status = (string) $t->status;
                                    $dot = $status === 'available' ? 'dot-available' : ($status === 'oncleaning' || $status === 'on cleaning' ? 'dot-cleaning' : 'dot-occupied');
                                    $label = $t->label . ' — ' . ucfirst(str_replace('oncleaning','on cleaning',$status));
                                @endphp
                                <option value="{{ $t->id }}" @if($status === 'occupied') disabled @endif>
                                    {{ $label }} 
                                    @if ($dot === 'available')
                                        <span class="status-dot dot-available"></span>
                                    @elseif($dot === 'oncleaning')
                                        <span><span class="status-dot dot-cleaning"></span></span>
                                    @elseif($dot === 'occupied')
                                        <span><span class="status-dot dot-occupied"></span></span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <!-- Legend -->
                        <div class="d-flex gap-3 mt-2 small">
                            <span><span class="status-dot dot-available"></span> Available</span>
                            <span><span class="status-dot dot-cleaning"></span> On Cleaning</span>
                            <span><span class="status-dot dot-occupied"></span> Occupied</span>
                        </div>
                    </div>
                @endif

                <hr>
                <div class="d-flex text-muted small mb-2 bg-info-subtle">
                    <div class="flex-grow-1">Item</div>
                    <div style="width:90px;" class="text-center">Qty</div>
                    <div style="width:110px;" class="text-end">Price</div>
                </div>

                @php($cartItems = $this->cartItems)
                <div class="d-flex flex-column gap-2">
                    @forelse($cartItems as $citem)
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $citem->name }}</div>
                                @if($citem->attributes && ($citem->attributes['options'] ?? false))
                                    <div class="small text-muted text-truncate">
                                        @foreach(($citem->attributes['options'] ?? []) as $opt)
                                            <span>{{ $opt['value'] }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div style="width:100px;" class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <div class="qty-bar mb-4">
                                        <button class="qty-btn pos" type="button" wire:click="decreaseItem({{ $citem->id }})"><i class="bi bi-dash"></i></button>
                                        <div class="qty-value">{{ $citem->quantity }}</div>
                                        <button class="qty-btn pos" type="button" wire:click="increaseItem({{ $citem->id }})"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div style="width:110px;" class="text-end fw-semibold">Rp{{ number_format((float)$citem->price * (int)$citem->quantity, 2) }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">Cart is empty.</div>
                    @endforelse
                </div>

                @php($sum = $this->cartSummary)
                <div class="mt-3 d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between"><span>Sub Total</span><span>Rp{{ number_format($sum['subtotal'] ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><span>Rp{{ number_format($sum['discount'] ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between fw-semibold fs-5"><span>Total</span><span>Rp{{ number_format($sum['total'] ?? 0, 2) }}</span></div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-main w-100" wire:click="checkout">Order</button>
                </div>
            </div> <!-- /.card-body -->
        </div> <!-- /.card -->
        </div> <!-- /.pos-right-column -->
        </div> <!-- /.row -->
    </div> <!-- /.container-fluid -->

    <!-- Product Options Modal (Custom overlay, no Bootstrap dependency) -->
    @if($showOptionModal && $selectedProduct)
        <div class="pospm-overlay" wire:click.self="closeOptionModal" wire:key="pospm-{{ $selectedProduct->id }}">
            <div class="pospm-modal">
                <button type="button" class="pospm-close" aria-label="Close" wire:click="closeOptionModal">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div class="pospm-header d-flex gap-3 align-items-start mb-3">
                    @if(!empty($selectedProduct->product_image_url))
                        <img src="{{ $selectedProduct->product_image_url }}" alt="{{ $selectedProduct->name }}" class="pospm-thumb">
                    @else
                        <div class="pospm-thumb d-flex align-items-center justify-content-center bg-light text-muted">No Image</div>
                    @endif
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="min-w-0">
                                <h5 class="mb-1 fw-semibold text-truncate">{{ $selectedProduct->name }}</h5>
                                @if(!empty($selectedProduct->description))
                                    <div class="text-muted small text-truncate-2">{{ $selectedProduct->description }}</div>
                                @endif
                            </div>
                            <div class="text-end ms-2 fw-semibold">Rp.{{ number_format((float)($selectedProduct->price ?? 0), 2) }}</div>
                        </div>
                    </div>
                </div>

                <div class="pospm-section mb-3">
                    <div class="fw-semibold mb-2">Quantity</div>
                    <div class="d-inline-flex align-items-center gap-2 border rounded-pill p-1 ps-2 pe-2 pospm-qty">
                        <button type="button" class="btn btn-sm btn-light rounded-circle pospm-qty-btn" wire:click="decrementQuantity"><i class="bi bi-dash-lg"></i></button>
                        <div class="fw-semibold mx-2" style="min-width: 24px; text-align:center;">{{ $quantity }}</div>
                        <button type="button" class="btn btn-sm btn-light rounded-circle pospm-qty-btn" wire:click="incrementQuantity"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                @if(!empty($selectedProduct->options) && $selectedProduct->options->count() > 0)
                    @foreach($selectedProduct->options as $option)
                        <div class="pospm-section mb-3">
                            <div class="fw-semibold mb-2">{{ $option->name }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($option->values as $value)
                                    <input
                                        class="btn-check"
                                        type="radio"
                                        id="opt-{{ $option->id }}-{{ $value->id }}"
                                        wire:model="selectedOptions.{{ $option->id }}"
                                        value="{{ $value->id }}"
                                    >
                                    <label class="btn pospm-pill" for="opt-{{ $option->id }}-{{ $value->id }}">
                                        <span>{{ $value->value }}</span>
                                        @if(($value->price_adjustment ?? 0) > 0)
                                            <span class="text-muted small">+Rp{{ number_format((float)$value->price_adjustment, 2) }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                @if (!empty($suggestedAddons))
                    <div class="pospm-section mb-3">
                        <div class="fw-semibold mb-2">Addons</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach($suggestedAddons as $ad)
                                <div class="pospm-addon d-flex align-items-center gap-2">
                                    @if(!empty($ad['image_url']))
                                        <img src="{{ $ad['image_url'] }}" class="pospm-addon-thumb" alt="{{ $ad['name'] }}">
                                    @else
                                        <div class="pospm-addon-thumb d-flex align-items-center justify-content-center bg-light text-muted">No</div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $ad['name'] }}</div>
                                        <div class="text-muted small">Rp.{{ number_format((float)($ad['price'] ?? 0), 2) }}</div>
                                    </div>
                                    <div class="d-inline-flex align-items-center gap-2 border rounded-pill p-1 ps-2 pe-2 pospm-qty">
                                        <button type="button" class="btn btn-sm btn-light rounded-circle pospm-qty-btn" wire:click="decAddon({{ $ad['id'] }})"><i class="bi bi-dash-lg"></i></button>
                                        <div class="fw-semibold mx-2" style="min-width: 20px; text-align:center;">{{ (int)($addonQty[$ad['id']] ?? 0) }}</div>
                                        <button type="button" class="btn btn-sm btn-light rounded-circle pospm-qty-btn" wire:click="incAddon({{ $ad['id'] }})"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pospm-section mb-4">
                    <div class="fw-semibold mb-2">Special Instructions</div>
                    <textarea class="form-control" rows="2" placeholder="Add note (extra mayo, cheese, etc.)" wire:model.defer="note"></textarea>
                </div>

                <div class="pospm-cta">
                    <button type="button" class="btn btn-main w-100 rounded-pill py-3" wire:click="addSelectedProductToCart">
                        Add to Cart - Rp{{ number_format($this->modalTotal, 2) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Customer Modal -->
    <div wire:ignore.self class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="addCustomer">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCustomerModalLabel">Add Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model.defer="newCustomerName" placeholder="Customer name">
                            @error('newCustomerName')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" wire:model.defer="newCustomerEmail" placeholder="name@example.com">
                            @error('newCustomerEmail')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" wire:model.defer="newCustomerGender">
                                <option value="">—</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('newCustomerGender')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primer">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
    $js('posMeasure', () => {
        const scroll = document.querySelector('.pos-products-scroll');
        const leftCol = document.querySelector('.pos-left-column');
        const rightCol = document.querySelector('.pos-right-column');

        if (window.innerWidth < 992) {
            [scroll, leftCol, rightCol].forEach((el) => {
                if (el) el.style.height = '';
            });
            return;
        }

        const reference = leftCol || rightCol || scroll;
        if (!reference) return;

        const rect = reference.getBoundingClientRect();
        const viewport = window.innerHeight || document.documentElement.clientHeight;
        const gap = 16; // keep slight spacing from footer edge
        const topOffset = rect.top || 96;
        const height = Math.max(300, viewport - topOffset - gap);

        [leftCol, rightCol, scroll].forEach((el) => {
            if (el) el.style.height = `${height}px`;
        });
    });

    $js('posSetActiveCat', (catId) => {
        const cid = (catId === undefined || catId === null || catId === '') ? 'all' : String(catId);
        document.querySelectorAll('#pos-cat-slider .pos-cat-btn').forEach(btn => {
            const id = String(btn.dataset.catId || 'all');
            btn.classList.toggle('active', id === cid);
        });
    });

    $js('posCatSlider', () => {
        const el = document.getElementById('pos-cat-slider');
        if (!el || !window.Splide) return;
        if (window.posCatSplide) { try { window.posCatSplide.destroy(true); } catch (e) {} }
        window.posCatSplide = new Splide(el, {
            type: 'slide',
            pagination: false,
            perPage: 6,
            gap: '2rem',
            arrows: false,
            drag: 'free',
            snap: true,
            breakpoints: {
                1400: { perPage: 5 },
                1200: { perPage: 4 },
                992:  { perPage: 3 },
                768:  { perPage: 2 }
            }
        });
        window.posCatSplide.on('mounted', () => setTimeout($js.posMeasure, 0));
        window.posCatSplide.on('resized', () => setTimeout($js.posMeasure, 0));
        window.posCatSplide.mount();
        el.addEventListener('click', (ev) => {
            const btn = ev.target.closest('.pos-cat-btn');
            if (btn) $js.posSetActiveCat(btn.dataset.catId);
        });
        try { $js.posSetActiveCat(@json($categoryId)); } catch (e) {}
    });

    // Initial mounts
    document.addEventListener('livewire:init', () => {
        document.body.classList.add('pos-no-page-scroll');
        $js.posMeasure();
        $js.posCatSlider();
        setTimeout(() => $js.posMeasure(), 50);
    });
    document.addEventListener('livewire:navigated', () => {
        document.body.classList.add('pos-no-page-scroll');
        $js.posMeasure();
        $js.posCatSlider();
    });
    window.addEventListener('resize', () => $js.posMeasure());
    const navToggle = document.getElementById('toggleNavigationBar');
    if (navToggle) navToggle.addEventListener('click', () => setTimeout($js.posMeasure, 300));
    window.addEventListener('beforeunload', () => document.body.classList.remove('pos-no-page-scroll'));

    // Livewire event to keep active state synced when category changes server-side
    Livewire.on('pos-category-updated', (payload) => {
        $nextTick(() => $js.posSetActiveCat(payload?.id ?? null));
    });

    // Modal close
    Livewire.on('pos-close-add-customer', () => {
        const m = document.getElementById('addCustomerModal');
        if (!m) return;
        try {
            const Modal = (window.bootstrap && window.bootstrap.Modal) ? window.bootstrap.Modal : null;
            if (!Modal) return;
            const inst = Modal.getInstance(m) || new Modal(m);
            inst.hide();
        } catch (e) { console.error(e); }
    });

    // Lightweight toast for quick flash messages
    Livewire.on('pos-flash', (payload) => {
        try {
            const type = (payload?.type || 'info').toLowerCase();
            const message = payload?.message || '';
            let el = document.getElementById('posFlashToast');
            if (!el) {
                el = document.createElement('div');
                el.id = 'posFlashToast';
                el.className = 'toast-success-container';
                el.style.position = 'fixed';
                el.style.bottom = '20px';
                el.style.right = '20px';
                document.body.appendChild(el);
            }
            const icon = type === 'success' ? 'check-circle-fill' : (type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill');
            el.innerHTML = `<div class="d-flex justify-content-between align-items-center">
                <span class="toast-success-icon"><i class="bi bi-${icon}"></i></span>
                <p class="m-0 ms-2 flex-grow-1 text-black"><strong>${message}</strong></p>
                <button class="close-toast btn text-muted">X</button>
            </div>
            <div class="toast-progress"></div>`;
            el.classList.add('show');
            el.classList.remove('hide');
            const progress = el.querySelector('.toast-progress');
            if (progress) { progress.style.width = '0%'; setTimeout(() => progress.style.width = '100%', 50); }
            const closer = el.querySelector('.close-toast');
            let timer = setTimeout(() => { el.classList.remove('show'); el.classList.add('hide'); }, 4500);
            if (closer) closer.onclick = () => { clearTimeout(timer); el.classList.remove('show'); el.classList.add('hide'); };
        } catch (e) { alert(payload?.message || ''); }
    });
    </script>
    @endscript
</div>
