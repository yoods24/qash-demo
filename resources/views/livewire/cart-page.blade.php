{{-- Component-scoped styles inside root to keep single Livewire root element --}}
<div class="cart-component-root">
<style>
    /* Accent + base */
    .bg-primer { background-color: #FF8343; color: #fff; }
    .bg-primer2 {
        background-color: #142566;
        color: white
    }
    .text-primer { color: #FF8343; }

    /* Page chrome */
    .cart-shell { max-width: 520px; }
    .page-header { border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; }

    /* Stepper */
    .stepper { font-size: 0.85rem; color: #6c757d; }
    .stepper .active { color: #FF8343; font-weight: 600; }

    .progress-thin {
        height: 6px; 
        background: #ececec; 
        border-radius: 999px; 
        overflow: hidden; 
    }

    .progress-thin .progress-bar { 
        height: 100%;
        width: 0%;
        animation: loadBar 2s ease forwards;
        background: #FF8343; 
    }

    @keyframes loadBar {
        from {
            width: 0%;
        }
        to {
            width: 40%;
        }
    }

    /* Cards */
    .soft-card { border: 0; border-radius: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); }
    .section-title { background: #FF8343; color: #fff; border-radius: 12px; padding: .55rem .9rem; font-weight: 600; }

    /* Item row */
    .item-img { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; }
    .qty-wrap { border: 1px solid #eee; border-radius: 999px; padding: 2px; }
    .btn-qty { width: 28px; height: 28px; border-radius: 50%; line-height: 1; padding: 0; }
    .btn-qty:hover { background: #fff5ef; border-color: #FF8343 !important; color: #FF8343; }

    /* Add more */
    .add-more { border: 1px dashed #FF8343; color: #FF8343; border-radius: 12px; }

    /* Sticky CTA */
    .sticky-cta { position: sticky; bottom: 0; background: #fff; padding: .75rem; box-shadow: 0 -6px 16px rgba(0,0,0,0.06); border-top-left-radius: 16px; border-top-right-radius: 16px; }

    .btn-place { 
        background: #FF8343; 
        border: 0; color: #fff; 
        padding: .85rem 1.25rem; 
        border-radius: 999px; 
        font-weight: 600;
        transition: all 0.3s;
    }
    @media (width < 576px) {
        .btn-place {
            padding: 0.6rem 0.8rem;
            font-size: smaller;
            transition: all 0.3 ease;
        }
    }
    .btn-place:hover { background:#f77330; }

    /* Helpers */
    .muted { color: #6c757d; }

    /* Centered confirm modal */
    .modal-overlay { position: fixed; inset:0; z-index:1200; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.25); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
    .modal-card { 
        background:#fff; 
        border-radius:16px; 
        width: calc(100% - 2rem); 
        max-width: 420px; 
        box-shadow: 0 10px 30px rgba(0,0,0,.15); 
        transition: all 0.3 ease;
    }
    .modal-card .header { padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0; font-weight:600; }
    .modal-card .body { 
        padding: 2rem 1.25rem; 

    }
    .modal-card .footer { padding: .75rem 1.25rem 1.25rem; display:flex; gap:.5rem; justify-content:flex-end; }
</style>
{{-- resources/views/livewire/cart-page.blade.php (revamped to match design) --}}
<div class="container cart-shell m-auto my-3 text-black">
    {{-- Header + Stepper --}}
    <div class="page-header bg-primer text-center p-3 mb-2 position-relative">
        <a href="{{ route('customer.order', ['tenant' => $tenantId ?? request()->route('tenant') ?? tenant('id')]) }}" class="position-absolute start-0 ms-3 text-white">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 bold">Checkout</h5>
    </div>
    <div class="px-2 mb-3">
        <div class="d-flex justify-content-between stepper mb-2">
            <span class="active">Cart</span>
            <span>Payment</span>
            <span>Confirmation</span>
        </div>
        <div class="progress-thin"><div class="progress-bar"></div></div>
    </div>

    {{-- Pickup Location --}}
    <div class="soft-card card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-tv-fill text-primer"></i>
                        <span class="fw-semibold">Table 18</span>
                    </div>
                    <div class="small text-muted">Do you want to change your Table?</div>
                </div>
                <button class="btn btn-sm btn-light border">Change</button>
            </div>
            <div class="rounded-3 mt-3 p-2" style="background:#fff7f2; color:#815f4e;">
                <i class="bi bi-clock me-1"></i>
                <small>Ready for pickup in approximately 20 minute</small>
            </div>
        </div>
    </div>

    {{-- Your Order --}}
    <div class="mb-2">
        <div class="section-title d-inline-block">Your Order</div>
    </div>

    {{-- Cart Items --}}
    @forelse($items as $item)
        <div class="soft-card card mb-2">
            <div class="card-body d-flex align-items-start">
                {{-- Product Image --}}
                @if($item->attributes->image)
                    <img src="{{ asset('storage/' . $item->attributes->image) }}" alt="{{ $item->name }}" class="item-img me-3">
                @else
                    <img src="{{ asset('default.png') }}" alt="No Image" class="item-img me-3">
                @endif

                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-3 align-items-center">
                            <h6 class="mb-1 fw-semibold">{{ $item->name }}</h6>
                            <i wire:click="editItem({{$item->id}})" class="cursor-pointer text-primer text-sm bi bi-pencil-square"></i>
                        </div>
                        <button wire:click="removeItem({{ $item->id }})" class="btn btn-sm p-0 text-muted"><i class="bi bi-x-lg"></i></button>
                    </div>

                    {{-- Options --}}
                    @if($item->attributes->options)
                        <ul class="list-unstyled small text-muted mb-2">
                            @foreach($item->attributes->options as $option)
                                <li>
                                    {{ $option['value'] }}
                                    @if(($option['price_adjustment'] ?? 0) > 0)
                                        (+Rp{{ number_format($option['price_adjustment'], 0) }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="qty-wrap d-inline-flex align-items-center gap-1">
                            <button wire:click="decreaseQty({{ $item->id }})" class="btn btn-light border btn-qty"><i class="bi bi-dash"></i></button>
                            <span class="mx-2">{{ $item->quantity }}</span>
                            <button wire:click="increaseQty({{ $item->id }})" class="btn btn-light border btn-qty"><i class="bi bi-plus"></i></button>
                        </div>
                        <span class="fw-semibold">Rp{{ number_format($item['attributes']['base_price'], 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="soft-card card mb-3">
            <div class="card-body text-center py-4">
                <div class="mb-2"><i class="bi bi-bag fs-3 text-primer"></i></div>
                <div class="fw-semibold mb-1">Your cart is empty</div>
                <div class="small text-muted mb-3">Let’s add something tasty.</div>
                <a href="{{ route('customer.order', ['tenant' => $tenantId ?? request()->route('tenant') ?? tenant('id')]) }}" class="btn btn-primer">Add Items</a>
            </div>
        </div>
    @endforelse

    {{-- Add More Items --}}
    <a href="{{ route('customer.order', ['tenant' => $tenantId ?? request()->route('tenant') ?? tenant('id')]) }}" class="add-more d-block text-center py-3 mb-3 text-decoration-none">
        + Add More Items
    </a>
    {{-- Add anything  --}}
    <div id="product-slider" class="splide" wire:ignore>
        <div class="splide__track ps-2 p-3 rounded">
            <h3 class="text-xl font-bold mb-3">People's favorite</h3>
            <ul class="splide__list">
                @foreach ($featuredProducts as $product)
                    <li class="splide__slide">
                        <div wire:click="showProductOptions({{ $product->id }})" class="d-flex gap-1 p-3 flex-row justify-content-between bg-white rounded-xl shadow product-card">
                            <div class="d-flex gap-3">
                                <div>
                                    <img src="{{ asset('storage/'. $product->product_image) }}"  
                                        class="product-img rounded-md" 
                                        alt="{{ $product->name }}">
                                </div>
                                <div class="text-start ms-sm-3 mt-3 mt-sm-0">
                                    <h5 class="mb-1 text-lg fw-semibold">{{ $product->name }}</h5>
                                    <p class="text-sm text-truncate text-muted">{{ Str::limit($product->description,20) }}</p>
                                    <p class="fw-bold mb-0">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
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
    
    {{-- Voucher + Payment --}}
    <div class="row g-2 mb-3">
        <div class="col-12">
            <div class="soft-card card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-percent text-primer"></i>
                        <span>Apply Voucher</span>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="soft-card card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-credit-card-2-front text-primer"></i>
                        <span>Payment Method</span>
                    </div>
                    <i class="bi bi-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Summary --}}
    <div class="mb-2">
        <div class="section-title d-inline-block">Payment Summary</div>
    </div>
    <div class="soft-card card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span class="muted">Subtotal</span>
                <span>Rp{{ number_format($total, 0) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="muted">Discount</span>
                <span class="text-success">- Rp 0</span>
            </div>
            @if(count($items) > 0)
            <div class="d-flex justify-content-between mb-2">
                <span class="muted">Software Service</span>
                <span>Rp{{ number_format($softwareService ?? 0, 0) }}</span>
            </div>
            @endif
            <hr>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Total</span>
                <span class="fw-bold text-primer">Rp{{ number_format($grandTotal, 0) }}</span>
            </div>
        </div>
    </div>

@if($showOptionModal && $selectedProduct)
<div id="modal-wrapper">
    <!-- Overlay -->
    <div 
        class="bottom-sheet-overlay" 
        onclick="@this.closeOptionModal()">
    </div>

    <!-- Modal -->
    <div class="bottom-sheet show">
        <!-- header buttons -->
        <div class="option-header d-flex justify-content-between">
            <button class="rounded-circle btn-lg bi bi-x " onclick="@this.closeOptionModal()"></button>
            <div class="d-flex gap-4">
                <button class="rounded-circle btn-lg bi bi-share "></button>
                <button class="rounded-circle btn-lg bi bi-heart "></button>
            </div>
        </div>
        {{-- product image --}}
        <div class="option-img-container">
            <img src="{{ asset('storage/' . $selectedProduct->product_image) }}" 
                class="img-fluid rounded mb-3 option-img">
        </div>

        <!-- Product Info -->
    <div class="product-info p-3">
        <div class="text-center">
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
                        <h6 class="price-accent">Rp {{ number_format($selectedProduct->price, 0, ',', '.') }}</h6>
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
                            <input class="option-radio" type="radio" id="opt-{{ $option->id }}-{{ $value->id }}" wire:model="selectedOptions.{{ $option->id }}" value="{{ $value->id }}">
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

        <!-- Quantity -->
        <div class="my-4">
            <p class="option-section-title mb-2">Quantity</p>
            <div class="qty-bar">
                <button class="qty-btn" type="button" wire:click="decrementQuantity"><i class="bi bi-dash"></i></button>
                <div class="qty-value text-black">{{ $quantity }}</div>
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
                    <div class="row g-2 text-black">
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
        <div class="d-flex flex-column mt-3 gap-3">
            <div class="input-group flex-grow-1">
                <button class="btn btn-outline-secondary" type="button" wire:click="decrementQuantity">-</button>
                <input type="number" class="form-control text-center" wire:model="quantity" disabled min="1">
                <button class="btn btn-outline-secondary" type="button" wire:click="incrementQuantity">+</button>
            </div>

            @if ($quantity > 0)
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
            @if ($quantity > 0)
                <button class="btn-cta" wire:click="addSelectedProductToCart">
                    {{ $editingItemId ? 'Update Item' : 'Add to Cart' }} — Rp {{ number_format($this->totalPrice, 0, ',', '.') }}
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

    {{-- Sticky CTA --}}
    <div class="sticky-cta">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="muted">Total</small>
                <div class="fw-bold">Rp{{ number_format($grandTotal, 0) }}</div>
            </div>
            <button wire:click="checkout" class="btn-place">Place Order • Rp {{ number_format($grandTotal, 0) }}</button>
        </div>
    </div>
</div>
{{-- Remove Item Modal --}}
@if($removeItemModal)
    <div class="modal-overlay" wire:click.self="cancelRemove">
        <div class="modal-card text-black">
            <div class="header bg-primer rounded">Remove Item</div>
            <div class="body">
                <p class="mb-0">Remove <span class="fw-semibold">{{ $removingItemName }}</span> from your cart?</p>
            </div>
            <div class="footer">
                <button class="btn btn-light border" wire:click="cancelRemove">Cancel</button>
                <button class="btn btn-danger" wire:click="confirmRemove">Remove</button>
            </div>
        </div>
    </div>
@endif
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

    // Run once when Livewire renders the component
    document.addEventListener("livewire:navigated", () => {
        $js.slider()
    });


    document.addEventListener('livewire:initialized',() => {
      const myElement = document.querySelector('.progress-bar');
      myElement.classList.add('loaded');
    });
</script>
@endscript
{{-- close artificial root --}}
</div>
