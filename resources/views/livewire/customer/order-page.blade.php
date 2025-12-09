
<div class="container py-4 order-container" wire:init="load">
    <div wire:loading.delay.short class="loadingAnimationLw">
        <img
            src="{{ global_asset('storage/logos/Logogram-Orange.png') }}"
            alt="Loading"
            style="width: 80px; height: 80px;"
            class="logo-spin"
        >
    </div>
    {{-- Banner Section --}}
    <div class="position-relative text-white rounded overflow-hidden mb-4" style="background: url('{{ global_asset('storage/ui/banner-coffee.png') }}') center/cover; min-height: 200px;">
        <div class="p-5" style="background: rgba(0,0,0,0.4); height: 100%;">
            <h5 class="fw-bold">Discover Your Perfect Brew!</h5>
            <h2 class="fw-bold">30% OFF</h2>
            <p>Limited Offer!</p>
        </div>
    </div>

    {{-- User Greeting + Order Box --}}
    <div class="bg-white rounded shadow p-3 mb-4">
        <div class="d-flex justify-content-between">
            <div class="d-flex flex-column">
                <span class="fw-bold text-dark">Hi, {{ $username ?? 'Guest' }} </span>
                <span class="text-muted">{{ $customerEmail ?? ''}}</span>
            </div>
                <div>
                @if (session()->has('customer_detail_id'))
                    <button wire:click="openCustomerEdit" class="btn btn-primer">Change data</button>
                @endif
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mt-3">
            <div class="text-uppercase small text-muted fw-semibold">Order Type</div>
            <div class="btn-group" role="group" aria-label="Order type toggle">
                <button type="button"
                    class="btn btn-sm {{ $orderType === 'dine-in' ? 'btn-primary' : 'btn-outline-primary' }}"
                    wire:click="selectOrderType('dine-in')">
                    Dine In
                </button>
                <button type="button"
                    class="btn btn-sm {{ $orderType === 'takeaway' ? 'btn-primary' : 'btn-outline-primary' }}"
                    wire:click="selectOrderType('takeaway')">
                    Takeaway
                </button>
            </div>
        </div>
        <hr>

        <div class="mt-3 align-content-center">
        {{-- Order Type Messaging --}}
        @if($orderType === 'dine-in')
            @if($currentTable)
                <div class="soft-card mb-3">
                    <div class="card-body border-none">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bi bi-tv-fill text-primer"></i>
                                    <span class="fw-semibold">Table {{ $currentTable }}</span>
                                </div>
                                <div class="small text-muted">Linked to this order.</div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">Dine In</span>
                        </div>
                        <div class="rounded-3 mt-3 p-2 d-flex justify-content-between text-center align-items-center" style="background:#fff7f2; color:#815f4e;">
                            <div>
                                <i class="bi bi-clock me-1"></i>
                                <small>Need to move to a different table?</small>
                            </div>
                            <button class="btn btn-sm btn-light border" type="button" wire:click="$set('showTableModal', true)">Change</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-3" role="alert">
                    <div>
                        <div class="fw-semibold text-dark">Scan your table</div>
                        <div class="small text-muted">Use the QR code on your table to continue dine-in ordering.</div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" type="button" wire:click="$set('showTableModal', true)">
                        Show instructions
                    </button>
                </div>
            @endif
        @else
            <div class="soft-card mb-3">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-bag text-warning"></i>
                            <span class="fw-semibold text-warning">Takeaway</span>
                        </div>
                        <div class="small text-muted">Pick up at the counter. We will notify you when items are ready.</div>
                    </div>
                    <span class="badge bg-warning text-dark">TAKEAWAY</span>
                </div>
            </div>
        @endif
        </div>
    </div>

    <div class="d-flex gap-3 mb-3 overflow-auto flex-nowrap sidebar-category">
        {{-- All Button --}}
<button 
    class="btn-sm flex-shrink-0 {{ $selectedCategory === 'all' ? 'btn-primer btn' : 'reservation-btn' }}" 
    wire:click="filterCategory('all')">
    All
</button>


        {{-- Dynamic Categories --}}
        @foreach ($categories as $category)
            <button 
                class="btn-sm flex-shrink-0 {{ $selectedCategory === $category->id ? 'btn-primer btn' : 'reservation-btn' }}" 
                wire:click="filterCategory({{ $category->id }})">
                {{ $category->name }}
            </button>      
        @endforeach
    </div>


    @if($availableDiscounts->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">Available Discounts</h4>
                <small class="text-muted">Updated daily</small>
            </div>
            <div id="voucher-slider" class="splide mt-3" wire:ignore>
                <div class="splide__track p-3">
                    <ul class="splide__list">
                        @foreach ($availableDiscounts as $discount)
                            <li class="splide__slide">
                                <div class="bg-white rounded-5 shadow p-3 h-100 discount-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-light text-dark text-capitalize mb-1">
                                                {{ $discount->discount_type }} 
                                            </span>
                                            <h5 class="mb-0 fw-bold text-dark">{{ $discount->name }}</h5>
                                        </div>
                                        <div class="text-end text-primer fw-bold fs-5">
                                            @if($discount->discount_type === 'percent')
                                                {{ rtrim(rtrim(number_format($discount->value, 2, '.', ''), '0'), '.') }}%
                                            @else
                                                {{ rupiahRp($discount->value) }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-muted small mb-3">
                                        {{ optional($discount->valid_from)->format('d M Y') }} - {{ optional($discount->valid_till)->format('d M Y') }}
                                    </div>
                                    @if($discount->applicable_for === 'specific')
                                        @php
                                            $products = collect($discount->available_products ?? []);
                                        @endphp
                                        <div class="d-flex gap-2 align-items-center">
                                            @foreach($products->take(3) as $product)
                                                @php $productImage = tenant_storage_url($product->product_image ?? null); @endphp
                                                <img src="{{ $productImage ?? 'https://via.placeholder.com/48' }}"
                                                     alt="{{ $product->name ?? '' }}"
                                                     class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                            @endforeach
                                            @if($products->count() > 3)
                                                <span class="small text-muted">+{{ $products->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-success-subtle text-success px-3 py-2">All Items</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if($loaded)
    @if($selectedCategory === 'all')
    {{-- Voucher Lists --}}

    {{-- Coffee List --}}
    <div id="product-slider" class="splide" wire:ignore>
        <div class="splide__track ps-2 p-3 rounded">
            <h3 class="text-xl font-bold mb-5">Featured Coffee</h3>
            <ul class="splide__list">
                @foreach ($featuredProducts as $product)
                    @php $isOut = (int)($product->stock_qty ?? 0) <= 0; @endphp
                    <li class="splide__slide">
                        <div wire:click="showProductOptions({{ $product->id }})" class="d-flex gap-1 p-3 flex-row justify-content-between bg-white rounded-xl shadow product-card position-relative {{ $isOut ? 'product-card-out' : '' }}">
                            @if($isOut)
                                <span class="badge bg-secondary-subtle text-secondary position-absolute top-50 start-50 translate-middle px-3 py-2" style="pointer-events: none;">
                                    Out of stock
                                </span>
                            @endif
                            <div class="d-flex gap-3">
                                <div>
                                    <img src="{{ $product->product_image_url ?? 'https://via.placeholder.com/160' }}"  
                                        class="product-img rounded-md" 
                                        alt="{{ $product->name }}">
                                </div>
                                <div class="text-start ms-sm-3 mt-3 mt-sm-0">
                                    <h3 class="mb-1 text-lg fw-semibold">{{ $product->name }}</h3>
                                    <p class="text-sm text-truncate text-muted">{{ Str::limit($product->description,20) }}</p>
                                    @php
                                        $discount = $product->discount_details ?? null;
                                        $hasDiscount = $discount['has_discount'] ?? false;
                                    @endphp
                                    @if($hasDiscount)
                                        <div class="d-flex flex-column">
                                            <span class="text-muted text-decoration-line-through small">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                                            <span class="fw-bold text-success">Rp{{ number_format($discount['final_price'], 0, ',', '.') }}</span>
                                            <span class="badge bg-success-subtle text-success mt-1">{{ $discount['badge'] }}</span>
                                        </div>
                                    @else
                                        <p class="fw-bold mb-0">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="add-btn-container d-flex flex-column justify-content-between">
                                <div>
                                    <i class="bi bi-share primer"></i>
                                </div>
                                <div>
                                    <button wire:click="showProductOptions({{ $product->id }})" class="mt-2 btn p-0 pe-1">
                                        <i class="bi bi-plus-circle-fill primer"></i> 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif



@foreach($products as $categoryData)
@php
    $categoryItems = collect($categoryData['items'] ?? []);
@endphp
<div class="product-line my-3"></div>
    {{-- Category Heading --}}
    <div class="d-flex justify-content-between align-items-center mb-0">
        <h5 class="fw-bold text-dark mb-0">{{ $categoryData['name'] }}</h5>
        <button wire:click="filterCategory({{ $categoryData['id'] }})" class="btn btn-link text-decoration-none text-muted">
            See all →
        </button>
    </div>
    <hr class="my-0">

    {{-- Product List --}}
    <div class="list-group product-list mb-5">
        @foreach($categoryItems as $product)
            @php $isOut = (int)($product->stock_qty ?? 0) <= 0; @endphp
            <div 
                wire:click="showProductOptions({{ $product->id }})"
                class="product-row d-flex align-items-center py-3 position-relative {{ $isOut ? 'product-row-out' : '' }}"
            >
                @if($isOut)
                    <span class="badge bg-secondary-subtle text-secondary position-absolute top-50 start-50 translate-middle px-3 py-2" style="pointer-events: none;">
                        Out of stock
                    </span>
                @endif
                {{-- Product Image --}}
                <img src="{{ $product->product_image_url ?? 'https://via.placeholder.com/96' }}" 
                    alt="{{ $product->name }}" 
                    class="product-thumb rounded">

                {{-- Text Info --}}
                <div class="flex-grow-1 ms-3">
                    <h6 class="fw-bold mb-1 text-dark">{{ $product->name }}</h6>
                    <p class="text-muted small mb-1">{{ Str::limit($product->description, 70) }}</p>
                    @php
                        $discount = $product->discount_details ?? null;
                        $hasDiscount = $discount['has_discount'] ?? false;
                    @endphp
                    @if($hasDiscount)
                        <div class="d-flex flex-column">
                            <span class="text-muted text-decoration-line-through small">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <span class="fw-bold text-success">Rp {{ number_format($discount['final_price'], 0, ',', '.') }}</span>
                            <span class="badge bg-success-subtle text-success align-self-start mt-1">{{ $discount['badge'] }}</span>
                        </div>
                    @else
                        <span class="fw-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="add-btn-container d-flex flex-column justify-content-between gap-5">
                    <div>
                        <i class="bi bi-heart primer"></i>
                    </div>
                    <div>
                        <button wire:click="showProductOptions({{ $product->id }})" class="mt-2 btn p-0 pe-1">
                            <i class="bi bi-plus-circle-fill primer"></i> 
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach

    @else
        <div class="text-center py-5 text-muted">
            Loading menu...
        </div>
    @endif


@if($showOptionModal && $selectedProduct)
<div id="modal-wrapper">
    <!-- Overlay -->
    <div 
        class="bottom-sheet-overlay" 
        onclick="@this.closeOptionModal()">
    </div>

    <!-- Modal -->
    <div class="bottom-sheet show" data-lenis-prevent>
        <!-- header buttons -->
        <div class="option-header d-flex justify-content-between">
            <button class="rounded-circle btn-lg bi bi-x p-2" onclick="@this.closeOptionModal()"></button>
            <div class="d-flex gap-4">
                <button class="rounded-circle btn-lg bi bi-share p-2"></button>
                <button class="rounded-circle btn-lg bi bi-heart p-2"></button>
            </div>
        </div>
        {{-- product image --}}
        <div class="option-img-container">
            <img src="{{ $selectedProduct->product_image_url ?? 'https://via.placeholder.com/320x200' }}" 
                class="img-fluid rounded mb-3 option-img">
        </div>

        <!-- Product Info -->
    <div class="product-info p-3">
        <div class="text-center">
                    @php $priceInfo = $this->selectedProductPriceInfo; @endphp
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="text-start w-75">
                            <h5 class="product-title mb-1">{{ $selectedProduct->name }}</h5>
                            <div class="d-flex align-items-center gap-2 product-subtle mb-1">
                                <i class="bi bi-star-fill text-warning"></i>
                                <span>4.8</span>
                                <span>(124 reviews)</span>
                            </div>
                            <p class="product-subtle mb-1">{{ $selectedProduct->description }}</p>
                        </div>
                        <div class="align-content-center">
                            @if($priceInfo['has_discount'])
                                <div class="text-end">
                                    <div class="text-muted text-decoration-line-through small">
                                        Rp {{ number_format($priceInfo['unit_raw'], 0, ',', '.') }}
                                    </div>
                                    <h6 class="price-accent text-success mb-0">
                                        Rp {{ number_format($priceInfo['unit_final'], 0, ',', '.') }}
                                    </h6>
                                    <span class="badge bg-success-subtle text-success mt-1">{{ $priceInfo['badge'] }}</span>
                                </div>
                            @else
                                <h6 class="price-accent">Rp {{ number_format($priceInfo['unit_final'], 0, ',', '.') }}</h6>
                            @endif
                        </div>
                    </div>
                <hr>
        </div>

        <!-- Options -->
        <div class="mt-3">
            @if ($selectedProduct->options->count() > 0)
                @foreach($selectedProduct->options as $option)
                    <p class="option-section-title mb-2">{{ $option->name }}</p>
                    <div class="option-grid mb-3">
                        @foreach($option->values as $value)
                        <div class="option-item">
                            <input 
                                class="option-radio"
                                type="radio" 
                                id="opt-{{ $option->id }}-{{ $value->id }}"
                                wire:model="selectedOptions.{{ $option->id }}" 
                                value="{{ $value->id }}">
                            <label class="option-card" for="opt-{{ $option->id }}-{{ $value->id }}">
                                <div class="opt-name">{{ $value->value }}</div>
                                <div class="opt-meta">
                                    @if($value->price_adjustment > 0)
                                        +Rp{{ number_format($value->price_adjustment, 0, ',', '.') }}
                                    @else
                                        Free
                                    @endif
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <p class="text-muted">No options available</p>
            @endif
        </div>
        <hr>

        <!-- Special Instructions -->
        <div class="mt-3">
            <label class="form-label">Special Instructions</label>
            <textarea class="form-control" rows="3" placeholder="Add note (extra mayo, cheese, etc.)" wire:model.defer="note"></textarea>
        </div>

        <!-- Quantity -->
        <div class="mt-3">
            <p class="option-section-title mb-2">Quantity</p>
            <div class="qty-bar mb-4">
                <button class="qty-btn" type="button" wire:click="decrementQuantity"><i class="bi bi-dash"></i></button>
                <div class="qty-value">{{ $quantity }}</div>
                <button class="qty-btn" type="button" wire:click="incrementQuantity"><i class="bi bi-plus"></i></button>
            </div>
        </div>

        <!-- Info Tabs: Ingredients, Nutrition, Reviews -->
        <div class="mt-2">
            <ul class="nav nav-tabs" id="productTabs-{{ $selectedProduct->id }}" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ingredients-tab-{{ $selectedProduct->id }}" data-bs-toggle="tab" data-bs-target="#ingredients-{{ $selectedProduct->id }}" type="button" role="tab" aria-controls="ingredients-{{ $selectedProduct->id }}" aria-selected="true">Ingredients</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="nutrition-tab-{{ $selectedProduct->id }}" data-bs-toggle="tab" data-bs-target="#nutrition-{{ $selectedProduct->id }}" type="button" role="tab" aria-controls="nutrition-{{ $selectedProduct->id }}" aria-selected="false">Nutrition</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab-{{ $selectedProduct->id }}" data-bs-toggle="tab" data-bs-target="#reviews-{{ $selectedProduct->id }}" type="button" role="tab" aria-controls="reviews-{{ $selectedProduct->id }}" aria-selected="false">Reviews</button>
                </li>
            </ul>
            <div class="tab-content pt-3" id="productTabsContent-{{ $selectedProduct->id }}">
                <div class="tab-pane fade show active" id="ingredients-{{ $selectedProduct->id }}" role="tabpanel" aria-labelledby="ingredients-tab-{{ $selectedProduct->id }}">
                    <ul class="list-unstyled small text-muted mb-0">
                        <li>Espresso shot</li>
                        <li>Steamed milk</li>
                        <li>Caramel syrup</li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="nutrition-{{ $selectedProduct->id }}" role="tabpanel" aria-labelledby="nutrition-tab-{{ $selectedProduct->id }}">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small">Calories</div>
                                <div class="fw-bold">120 kcal</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small">Protein</div>
                                <div class="fw-bold">3g</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small">Carbs</div>
                                <div class="fw-bold">12g</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="text-muted small">Fat</div>
                                <div class="fw-bold">5g</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="reviews-{{ $selectedProduct->id }}" role="tabpanel" aria-labelledby="reviews-tab-{{ $selectedProduct->id }}">
                    <div class="small text-muted">“Great balance, not too sweet.”</div>
                    <div class="small text-muted">“Smooth and strong!”</div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Quantity and Add to Cart -->
        @php $soldOut = $this->selectedProductSoldOut; @endphp
        <div class="d-flex flex-column mt-3 gap-3">
            <div class="input-group flex-grow-1">
                <button class="btn btn-outline-secondary" type="button" wire:click="decrementQuantity" @disabled($soldOut)>-</button>
                <input type="number" class="form-control text-center" wire:model="quantity" disabled min="1">
                <button class="btn btn-outline-secondary" type="button" wire:click="incrementQuantity" @disabled($soldOut)>+</button>
            </div>

            @if ($soldOut)
                <button class="btn btn-secondary btn-sm w-100 w-sm-auto" disabled>
                    Product Sold Out
                </button>
            @elseif ($quantity > 0)
                <button class="reservation-btn btn-sm w-100 w-sm-auto" wire:click="addSelectedProductToCart">
                    Add to Cart — Rp {{ number_format($this->totalPrice, 0, ',', '.') }}
                </button>
            @else
                <button disabled class="btn-secondary btn-sm w-100 w-sm-auto">
                    Add Quantity First!
                </button>
            @endif
        </div>
        </div>
                <!-- Sticky CTA inside modal -->
        <div class="option-cta" id="option-cta">
            @if ($soldOut)
                <button class="btn-cta disabled" disabled>
                    Product Sold Out
                </button>
            @elseif ($quantity > 0)
                <button class="btn-cta" wire:click="addSelectedProductToCart">
                    Add to Cart — Rp {{ number_format($this->totalPrice, 0, ',', '.') }}
                </button>
            @else
                <button class="btn-cta" disabled>
                    Add Quantity First!
                </button>
            @endif
 </div>
 </div>
</div>
@endif

@if($showSwitchOrderTypeModal)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Switch to Takeaway?</h5>
        <button type="button" class="btn-close" aria-label="Close" wire:click="cancelOrderTypeSwitch"></button>
      </div>
      <div class="modal-body">
        <p>You are currently assigned to <strong>{{ $currentTable ? 'Table ' . $currentTable : 'a table' }}</strong>.</p>
        <p class="mb-0">If you continue, the table assignment will be removed and your order will be treated as takeaway.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" wire:click="cancelOrderTypeSwitch">Cancel</button>
        <button type="button" class="btn btn-danger" wire:click="confirmOrderTypeSwitch">Confirm</button>
      </div>
    </div>
  </div>
</div>
<style>
  body { overflow: hidden; }
</style>
@endif

@if($showTableModal)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.4);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scan Table First</h5>
        <button type="button" class="btn-close" aria-label="Close" wire:click="closeTableModal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Please scan the QR code on your dining table before adding items to your cart.</p>
        <small class="text-muted">Tip: Open your phone camera and scan the table QR. This page will automatically detect your table when opened from the QR.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primer" wire:click="closeTableModal">OK</button>
      </div>
    </div>
  </div>
</div>
<style>
  /* prevent page scroll when modal visible */
  body { overflow: hidden; }
</style>
@endif

{{-- Customer Details Modal --}}
@if($showCustomerModal)
<div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Your Details</h5>
        <button type="button" class="btn-close" aria-label="Close" wire:click="cancelCustomerModal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" wire:model.defer="customerName" placeholder="Enter your name">
          @error('customerName')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" wire:model.defer="customerEmail" placeholder="you@example.com">
          @error('customerEmail')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Coffee Shop</label>
          <input type="text" class="form-control" wire:model.defer="tenantName" readonly>
          <input type="hidden" wire:model="tenantId">
        </div>
        <div class="mb-3">
          <label class="form-label">Gender (optional)</label>
          <div class="d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" id="genderMale" value="male" wire:model="customerGender">
              <label class="form-check-label" for="genderMale">Male</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="genderFemale" value="female" wire:model="customerGender">
              <label class="form-check-label" for="genderFemale">Female</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" id="genderNone" value="none" wire:model="customerGender">
              <label class="form-check-label" for="genderNone">Prefer not to say</label>
            </div>
          </div>
          @error('customerGender')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" wire:click="cancelCustomerModal">Cancel</button>
        <button type="button" class="btn btn-primer" wire:click="saveCustomer">
            {{ $pendingAction === 'add_to_cart' ? 'Save & Add to Cart' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
  <style>
    /* prevent page scroll when modal visible */
    body { overflow: hidden; }
  </style>
</div>
@endif

@if ($cartQuantity > 0)
<div class="cart-footer">
    <div class="eta">
        <p class="mb-0">Estimated Time 20 Minutes</p>
    </div>
    <a href="{{ route('cart.page', ['tenant' => $tenantId ?? request()->route('tenant') ?? tenant('id')]) }}" class="cart-summary">
        <div class="cart-info p-3 d-flex w-100 justify-content-between">
            <div class="d-flex gap-2">
                <i class="bi bi-cart-check"></i>
                <p class="mb-0">({{ $cartQuantity }} produk)</p>
            </div>
            <p class="cart-total mb-0">Rp {{ number_format($cartTotal, 0, ',', '.') }}</p>
        </div>
    </a>
</div>
@endif

<!-- QR Scanner Modal -->
<div id="qr-scanner-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div id="qr-reader" style="width: 300px"></div>
        <button onclick="stopScanner()">Close</button>
    </div>
</div>

@script
<script>
    // define $js.slider once
    $js('slider', () => {
        console.log('splide mount called');

        if (window.splideInstance) {
            window.splideInstance.destroy(true);
        }

        let el = document.querySelector('#product-slider');
        if (el) {
            window.splideInstance = new Splide(el, {
                autoplay: false,
                perPage: 1,
                gap: '3rem',
                breakpoints: {
                    768:{
                        gap:'1rem'
                    }
                }
            });
            window.splideInstance.mount();
        }
    });

    // 👇 only mount after filterCategory finishes re-render
    $wire.on('categoryUpdated', () => {
        $nextTick(() => {
            console.log("Category updated + DOM patched, now mount Splide...");
            $js.slider();
            $js.voucherSlider();
        });
    });
    // Run once when Livewire renders the component
    document.addEventListener("livewire:navigated", () => {
        $js.slider()
    });

    // voucher
$js('voucherSlider', () => {
    console.log('Voucher slider mount called');

    if (window.voucherSplideInstance) {
        window.voucherSplideInstance.destroy(true);
    }

    let el = document.querySelector('#voucher-slider');
    if (el) {
        window.voucherSplideInstance = new Splide(el, {
            autoplay: true,
            perPage: 1,
            gap: '2rem',
            breakpoints: {
                768: {
                    perPage: 1
                }
            }
        });
        window.voucherSplideInstance.mount();
    }
});

// Run voucher slider once when component is rendered
document.addEventListener("livewire:navigated", () => {
    $js.voucherSlider();
    $js.initCartFooter();

});

$wire.on('cart-updated', () => {
    $nextTick(() => {
        $js.initCartFooter();
    })
})

// 👉 Cart footer logic in reusable function
$js('initCartFooter', () => {
    let cartFooter = document.querySelector('.cart-footer');
    if (!cartFooter) return;

    console.log("Cart footer initialized");

    // show with a little slide-up delay
    setTimeout(() => {
        cartFooter.classList.add('show');
    }, 100);

    let lastScrollY = window.scrollY;
    let scrollTimeout;

    window.removeEventListener('scroll', handleCartFooterScroll); // prevent double-binding
    window.addEventListener('scroll', handleCartFooterScroll);

    function handleCartFooterScroll() {
        if (!cartFooter) return;

        if (window.scrollY > lastScrollY) {
            cartFooter.classList.add('hide');
        } else {
            cartFooter.classList.remove('hide');
        }

        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            cartFooter.classList.remove('hide');
        }, 1000);

        lastScrollY = window.scrollY;
    }
})

</script>
@endscript
