<div class="pos-page">
        <div wire:loading.delay.short class="loadingAnimationLw">
        <img
            src="{{ global_asset('storage/logos/Logogram-Orange.png') }}"
            alt="Loading"
            style="width: 80px; height: 80px;"
            class="logo-spin"
        >
    </div>
    <div class="container-fluid bg-main">
        <div class="row g-3">
            <!-- Products grid (scrollable area) -->
            <div class="col-md-7 col-lg-8 pos-left-column d-flex flex-column" wire:init="loadProducts">
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
                                        @php
                                            $discount = $product->discount_details ?? null;
                                            $hasDiscount = $discount['has_discount'] ?? false;
                                        @endphp
                                        <div class="mb-2">
                                            @if($hasDiscount)
                                                <div class="d-flex flex-column align-items-start">
                                                    <span class="text-muted text-decoration-line-through small">Rp{{ number_format((float)($product->price ?? 0), 0, ',', '.') }}</span>
                                                    <span class="fw-bold text-success">Rp{{ number_format((float)($discount['final_price'] ?? 0), 0, ',', '.') }}</span>
                                                    <span class="badge bg-success-subtle text-success mt-1">{{ $discount['badge'] }}</span>
                                                </div>
                                            @else
                                                <div class="fw-semibold">Rp{{ number_format((float)($product->price ?? 0), 0, ',', '.') }}</div>
                                            @endif
                                            @if($product->stock_qty !== null)
                                                <span class="text-muted small d-block">Stock: {{ max(0, $stockQty) }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-auto text-end">
                                            <button
                                                type="button"
                                                class="btn btn-sm w-100 {{ $isOutOfStock ? 'btn-outline-secondary disabled' : 'btn-secondary-main' }}"
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
                                <div class="alert alert-secondary">Loading.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right pane: customer + order type + table + bill summary -->
            <div class="col-md-5 col-lg-4 pos-right-column">
                <div class="card h-100">
                    <div class="card-body" data-lenis-prevent>
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
                    <div>
                        <label class="form-label mb-0">Customer</label>
                        <div class="text-muted small">Selected: {{ $this->selectedCustomerLabel }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-success btn-sm" wire:click="openCustomerSearch">
                            <i class="bi bi-search"></i> Find
                        </button>
                        <button type="button" class="btn btn-sm btn-main" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-2"><label class="form-label">Select Order Type</label></div>
                <div class="btn-group mb-3 w-100" role="group">
                    <input type="radio" class="btn-check" name="orderType" id="ot-dine" value="dine-in" autocomplete="off" wire:model.live="orderType">
                    <label class="btn {{ $orderType === 'dine-in' ? 'btn-secondary-main' : 'btn-outline-secondary-main' }}" for="ot-dine"><i class="bi bi-shop"></i> Dine-In</label>

                    <input type="radio" class="btn-check" name="orderType" id="ot-take" value="takeaway" autocomplete="off" wire:model.live="orderType">
                    <label class="btn {{ $orderType === 'takeaway' ? 'btn-secondary-main' : 'btn-outline-secondary-main' }}" for="ot-take"><i class="bi bi-bag"></i> Takeaway</label>
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
                <div class="d-flex justify-content-between small mb-2 pos-cart-header">
                    <div>Item</div>
                    <div>Qty</div>
                    <div>Price</div>
                </div>

                @php
                    $cartItems = $this->cartItems;
                @endphp
                <div class="d-flex flex-column gap-2">
                    @forelse($cartItems as $citem)
                        @php
                            $rawPrice = (float) ($citem->attributes['raw_price'] ?? $citem->price);
                            $unitPrice = (float) $citem->price;
                            $qty = (int) $citem->quantity;
                            $hasLineDiscount = ($citem->attributes['discount_amount'] ?? 0) > 0;
                            $discountBadge = $citem->attributes['discount_badge'] ?? null;
                        @endphp
                        <div class="d-flex justify-content-between">
                            <div class="w-25">
                                <div class="d-flex justify-content-between">
                                    <div class="text-truncate fw-semibold">
                                        {{ $citem->name }}
                                    </div>
                                    <button type="button" class="btn btn-action-edit btn-sm p-0 mt-1" wire:click="editCartItem('{{ $citem->id }}')">
                                        <i class="bi bi-pencil-square text-primer"></i>
                                    </button>
                                </div>
                                @if($citem->attributes && ($citem->attributes['options'] ?? false))
                                    <div class="small text-muted text-truncate">
                                        @foreach(($citem->attributes['options'] ?? []) as $opt)
                                            <span>{{ $opt['value'] }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($citem->attributes['note']))
                                    <div class="small text-muted fst-italic text-truncate">Note : {{ $citem->attributes['note'] }}</div>
                                @endif
                            </div>
                            <div style="width:100px;" class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <div class="qty-bar mb-4">
                                        <button class="qty-btn btn-main pos" type="button" wire:click="decreaseItem('{{ $citem->id }}')"><i class="bi bi-dash"></i></button>
                                        <div class="qty-value">{{ $citem->quantity }}</div>
                                        <button class="qty-btn btn-main pos" type="button" wire:click="increaseItem('{{ $citem->id }}')"><i class="bi bi-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div style="width:110px;" class="text-end fw-semibold">
                                @if($hasLineDiscount)
                                    <div class="text-muted text-decoration-line-through small">Rp{{ number_format($rawPrice * $qty, 0, ',', '.') }}</div>
                                    <div>Rp{{ number_format($unitPrice * $qty, 0, ',', '.') }}</div>
                                    @if($discountBadge)
                                        <span class="badge bg-success-subtle text-success small">{{ $discountBadge }}</span>
                                    @endif
                                @else
                                    Rp{{ number_format($unitPrice * $qty, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">Cart is empty.</div>
                    @endforelse
                </div>
                <hr class="qash-hr">
                @php
                    $sum = $this->cartSummary;
                @endphp
                <div class="mt-3 d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between"><span>Sub Total</span><span class="pos-summary-amount text-start ">Rp{{ number_format($sum['subtotal'] ?? 0, 0, ',', '.') }}</span></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><span class="pos-summary-amount text-start text-success">Rp{{ number_format($sum['discount'] ?? 0, 0, ',', '.') }}</span></div>
                    @foreach(($sum['tax_lines'] ?? []) as $line)
                        <div class="d-flex justify-content-between">
                            <span>{{ $line['name'] }}</span>
                            <span class="pos-summary-amount text-start">Rp{{ number_format($line['amount'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between fw-semibold fs-5"><span>Total</span><span class="pos-summary-amount text-start">{{ rupiahRp(roundToIndoRupiahTotal($sum['total'] ?? 0)) }}</span></div>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-main w-100" wire:click="openPaymentModal">Order</button>
                </div>
            </div> <!-- /.card-body -->
        </div> <!-- /.card -->
        </div> <!-- /.pos-right-column -->
        </div> <!-- /.row -->
    </div> <!-- /.container-fluid -->

    <!-- Product Options Modal (Custom overlay, no Bootstrap dependency) -->
    @if($showOptionModal && $selectedProduct)
        <div class="pospm-overlay" wire:click.self="closeOptionModal" wire:key="pospm-{{ $selectedProduct->id }}" data-lenis-prevent>
            <div class="pospm-modal">
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="pospm-close" aria-label="Close" wire:click="closeOptionModal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <hr>

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
                            @php
                                $spDiscount = $selectedProduct->discount_details ?? null;
                                $spHasDiscount = $spDiscount['has_discount'] ?? false;
                            @endphp
                            <div class="text-end ms-2 fw-semibold">
                                @if($spHasDiscount)
                                    <div class="text-muted text-decoration-line-through small">Rp{{ number_format((float)($selectedProduct->price ?? 0), 0, ',', '.') }}</div>
                                    <div class="text-success fw-bold">Rp{{ number_format((float)($spDiscount['final_price'] ?? 0), 0, ',', '.') }}</div>
                                    <span class="badge bg-success-subtle text-success mt-1">{{ $spDiscount['badge'] }}</span>
                                @else
                                    Rp{{ number_format((float)($selectedProduct->price ?? 0), 0, ',', '.') }}
                                @endif
                            </div>
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

                <div class="pospm-section mb-4">
                    <div class="fw-semibold mb-2">Special Instructions</div>
                    <textarea class="form-control" rows="2" placeholder="Add note (extra mayo, cheese, etc.)" wire:model.defer="note"></textarea>
                </div>

                <div class="pospm-cta">
                    <button type="button" class="btn btn-main w-100 rounded-pill py-3" wire:click="addSelectedProductToCart">
                        {{ $editingItemId ? 'Update Cart' : 'Add to Cart' }} - Rp{{ number_format($this->modalTotal, 0, ',', '.') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if($showCustomerSearch)
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Find Customer</h5>
                        <button type="button" class="btn-close" wire:click="closeCustomerSearch" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="input-group mb-3">
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Search by name or email"
                                wire:model.live.debounce.300ms="customerSearch"
                                wire:keydown.enter.prevent="performCustomerSearch"
                                autofocus
                            >
                            <button class="btn btn-main" type="button" wire:click="performCustomerSearch">
                                Filter
                            </button>
                        </div>

                        <div class="list-group">
                            @forelse($customerSearchResults as $cust)
                                <button
                                    type="button"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                    wire:click="selectCustomerFromSearch({{ $cust['id'] }})"
                                >
                                    <div>
                                        <div class="fw-semibold">{{ $cust['name'] }}</div>
                                        @if(!empty($cust['email']))
                                            <div class="small text-muted">{{ $cust['email'] }}</div>
                                        @endif
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </button>
                            @empty
                                <div class="text-muted small px-2 py-1">
                                    @if(strlen(trim($customerSearch)) < 2)
                                        Type at least 2 characters to search.
                                    @else
                                        No customers found.
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeCustomerSearch">Close</button>
                    </div>
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

    @if($showPaymentModal)
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" data-lenis-prevent>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order Payment</h5>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="$set('showPaymentModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Amount</span>
                                <strong class="fs-5">{{ rupiahRp(roundToIndoRupiahTotal($this->totalAmount)) }}</strong>
                            </div>
                        </div>

                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn {{ $paymentMethod === 'cash' ? 'btn-main' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'cash')">
                                <i class="bi bi-cash-stack me-1"></i> Cash
                            </button>
                            <button type="button" class="btn {{ $paymentMethod === 'card' ? 'btn-main' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'card')">
                                <i class="bi bi-credit-card-2-front me-1"></i> Card
                            </button>
                            <button type="button" class="btn {{ $paymentMethod === 'qris' ? 'btn-main' : 'btn-outline-secondary' }}" wire:click="$set('paymentMethod', 'qris')">
                                <i class="bi bi-qr-code me-1"></i> Qris
                            </button>
                        </div>

                        @if($paymentMethod === 'cash')
                            <div x-data="posCashPad({ total: {{ roundToIndoRupiahTotal($this->totalAmount) }} })" x-cloak>
                                <div class="mb-3">
                                    <label class="form-label">Enter Received Amount</label>
                                    <x-inputs.rupiah
                                        wireless
                                        placeholder="0"
                                        x-ref="cashInput"
                                        x-on:input="onInput($event)"
                                        x-bind:value="formatted"
                                    />
                                </div>

                                <div class="row g-2 mb-3">
                                    @foreach([1,2,3,4,5,6,7,8,9,'00',0] as $digit)
                                        <div class="col-4">
                                            <button type="button" class="btn btn-light w-100" x-on:click="append('{{ $digit }}')">{{ $digit }}</button>
                                        </div>
                                    @endforeach
                                    <div class="col-4">
                                        <button type="button" class="btn btn-outline-danger w-100" x-on:click="clear()">Clear</button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <span>Change</span>
                                    <strong x-text="formatCurrency(change)"></strong>
                                </div>

                                <div class="mt-3 d-flex justify-content-end gap-3">
                                <button type="button" class="btn btn-outline-secondary" wire:click="$set('showPaymentModal', false)">Cancel</button>
                                    <button
                                        type="button"
                                        class="btn btn-success"
                                        x-bind:disabled="numeric < total"
                                        x-on:click="$wire.confirmCashPaymentClient(formatted)"
                                    >
                                        Confirm &amp; Complete
                                    </button>
                                </div>
                            </div>
                        @elseif($paymentMethod === 'card')
                            <p class="mb-0">Proceed with card payment. Confirm when the transaction is successful.</p>
                        @elseif($paymentMethod === 'qris')
                            <p class="mb-0">Proceed with QRIS payment. Confirm when the customer has paid.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($paymentMethod === 'card')
                            <button type="button" class="btn btn-main" wire:click="confirmCardPayment">
                                Confirm Card Payment
                            </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showPaymentModal', false)">Cancel</button>

                        @elseif($paymentMethod === 'qris')
                            <button type="button" class="btn btn-main" wire:click="confirmQrisPayment">
                                Confirm QRIS Payment
                            </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showPaymentModal', false)">Cancel</button>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showReceiptModal && $receiptData)
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" data-lenis-prevent>
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receipt</h5>
                        <button type="button" class="btn-close" aria-label="Close" wire:click="closeReceiptModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <div class="fw-semibold">{{ $receiptData['reference'] ?? '—' }}</div>
                            <div class="text-muted small">{{ $receiptData['paid_at'] ?? '' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between"><span>Customer</span><span class="fw-semibold">{{ $receiptData['customer_name'] ?? 'Walk-in' }}</span></div>
                            @if(!empty($receiptData['customer_email']))
                                <div class="d-flex justify-content-between text-muted small"><span>Email</span><span>{{ $receiptData['customer_email'] }}</span></div>
                            @endif
                            @if(!empty($receiptData['order_type']))
                                <div class="d-flex justify-content-between text-muted small"><span>Order Type</span><span>{{ $receiptData['order_type'] }}</span></div>
                            @endif
                            @if(!empty($receiptData['customer_table']))
                                <div class="d-flex justify-content-between text-muted small"><span>Table</span><span>{{ $receiptData['customer_table'] }}</span></div>
                            @endif
                        </div>
                        <hr>
                        <div class="mb-3">
                            @foreach($receiptData['items'] ?? [] as $item)
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="fw-semibold">{{ $item['name'] ?? '' }}</div>
                                        <div class="text-muted small">Qty {{ $item['quantity'] ?? 0 }} @ Rp{{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</div>
                                        @if(!empty($item['options']))
                                            <div class="text-muted small">
                                                @foreach($item['options'] as $opt)
                                                    @php
                                                        $val = is_array($opt) ? ($opt['value'] ?? '') : (string) $opt;
                                                        $adj = is_array($opt) && isset($opt['price_adjustment']) ? (float) $opt['price_adjustment'] : null;
                                                    @endphp
                                                    <div>- {{ $val }}@if($adj && $adj > 0) (+Rp{{ number_format($adj, 0, ',', '.') }})@endif</div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if(!empty($item['note']))
                                            <div class="text-muted small">Note: {{ $item['note'] }}</div>
                                        @endif
                                    </div>
                                    <div class="fw-semibold">Rp{{ number_format($item['line_total'] ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <hr class="my-2">
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span>Rp{{ number_format($receiptData['subtotal'] ?? 0, 0, ',', '.') }}</span></div>
                        @foreach($receiptData['tax_lines'] ?? [] as $tax)
                            <div class="d-flex justify-content-between text-muted small"><span>{{ $tax['name'] ?? 'Tax' }}</span><span>Rp{{ number_format($tax['amount'] ?? 0, 0, ',', '.') }}</span></div>
                        @endforeach
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total</span><span>Rp{{ number_format($receiptData['grand_total'] ?? 0, 0, ',', '.') }}</span></div>
                        @if(!empty($receiptData['received']))
                            <div class="d-flex justify-content-between mt-1"><span>Received</span><span>Rp{{ number_format($receiptData['received'] ?? 0, 0, ',', '.') }}</span></div>
                        @endif
                        @if(!empty($receiptData['change']))
                            <div class="d-flex justify-content-between text-success"><span>Change</span><span>Rp{{ number_format($receiptData['change'] ?? 0, 0, ',', '.') }}</span></div>
                        @endif
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeReceiptModal">Close</button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success" wire:click="sendReceiptEmail" @disabled(empty($receiptData['customer_email']))>
                                <i class="bi bi-envelope"></i> Email Receipt
                            </button>
                            <button type="button" class="btn btn-main" wire:click="printReceipt">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

    Livewire.on('pos-print-receipt', (payload) => {
        const html = payload?.html || '';
        if (!html) return;
        const w = window.open('', '_blank', 'width=480,height=720');
        if (!w) {
            alert('Please allow popups to print the receipt.');
            return;
        }
        w.document.write(html);
        w.document.close();
        w.focus();
        w.print();
    });

    // Indonesian rounding helper: round to 2 decimals (>=0.005 up) then to full rupiah (>=0.50 up)
    window.roundToIndoRupiahTotal = function (amount = 0) {
        const base = Math.max(0, Number(amount) || 0);
        const twoDecimals = Math.round(base * 100) / 100;
        return Math.round(twoDecimals);
    };

    // Cash keypad helper to avoid Livewire calls per click
    window.posCashPad = function payload(config = {}) {
        return {
            total: window.roundToIndoRupiahTotal(config.total || 0),
            digits: '',
            formatter: new Intl.NumberFormat('id-ID'),
            append(digit) {
                const clean = String(digit ?? '').replace(/\D/g, '');
                this.digits = `${this.digits}${clean}`;
            },
            clear() {
                this.digits = '';
            },
            onInput(ev) {
                const val = (ev?.target?.value ?? '').toString();
                this.digits = val.replace(/\D/g, '');
                if (ev?.target) ev.target.value = this.formatted;
            },
            get numeric() {
                return this.digits === '' ? 0 : Number(this.digits);
            },
            get formatted() {
                return this.digits === '' ? '' : this.formatter.format(this.numeric);
            },
            get change() {
                return window.roundToIndoRupiahTotal(Math.max(0, this.numeric - this.total));
            },
            formatCurrency(value) {
                return 'Rp ' + this.formatter.format(window.roundToIndoRupiahTotal(value));
            },
        };
    };
    </script>
    @endscript
</div>
