
<div class="container py-4 order-container">
    <div wire:loading.delay.short class="loadingAnimation">
        <dotlottie-wc
            src="https://lottie.host/926a98d0-7bf6-4990-8609-25ee02a687a1/N4xc3Fyms2.lottie"
            style="width: 100px; height: 50px"
            speed="1"
            autoplay
            loop
        ></dotlottie-wc>
    </div>
    {{-- Banner Section --}}
    <div class="position-relative text-white rounded overflow-hidden mb-4" style="background: url('{{ asset('storage/ui/banner-coffee.png') }}') center/cover; min-height: 200px;">
        <div class="p-5" style="background: rgba(0,0,0,0.4); height: 100%;">
            <h5 class="fw-bold">Discover Your Perfect Brew!</h5>
            <h2 class="fw-bold">30% OFF</h2>
            <p>Limited Offer!</p>
        </div>
    </div>

    {{-- User Greeting + Order Box --}}
    <div class="bg-white rounded shadow p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark">Hi, {{ $username ?? 'Guest' }}</span>
            <span class="badge bg-warning text-dark">Points: {{ $points ?? 0 }}</span>
        </div>

        <div class="mt-3 align-content-center">
            <p class="text-black bold h3 mb-4">Oops your not on a table yet</p>
            <button wire:click="Scan" class="btn btn-dark">Scan QR</button>
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


    @if($selectedCategory === 'all')
            {{-- Voucher Lists --}}

    {{-- Coffee List --}}
    <div id="product-slider" class="splide" wire:ignore>
        <div class="splide__track ps-2 p-3 rounded">
            <h3 class="text-xl font-bold mb-5">Featured Coffee</h3>
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
                                    <h3 class="mb-1 text-lg fw-semibold">{{ $product->name }}</h3>
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
    @else
    @endif



@foreach($products as $categoryData)
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
        @foreach($categoryData['items'] as $product)
            <div 
                wire:click="showProductOptions({{ $product->id }})"
                class="product-row d-flex align-items-center py-3"
            >
                {{-- Product Image --}}
                <img src="{{ asset('storage/' . $product->product_image) }}" 
                    alt="{{ $product->name }}" 
                    class="product-thumb rounded">

                {{-- Text Info --}}
                <div class="flex-grow-1 ms-3">
                    <h6 class="fw-bold mb-1 text-dark">{{ $product->name }}</h6>
                    <p class="text-muted small mb-1">{{ Str::limit($product->description, 70) }}</p>
                    <span class="fw-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
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
            <button class="rounded-circle btn-lg bi bi-x" onclick="@this.closeOptionModal()"></button>
            <div class="d-flex gap-4">
                <button class="rounded-circle btn-lg bi bi-share"></button>
                <button class="rounded-circle btn-lg bi bi-heart"></button>
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
                <div class="d-flex justify-content-between">
                    <div class="text-start w-75">
                        <h5 class="fw-bold">{{ $selectedProduct->name }}</h5>
                        <p class="text-muted mb-1 ">{{ $selectedProduct->description }}</p>
                    </div>
                    <div class="align-content-center">
                        <h6 class="text-brown">Rp {{ number_format($selectedProduct->price, 0, ',', '.') }}</h6>
                    </div>
                </div>
                <hr>
        </div>

        <!-- Options -->
        <div class="mt-3">
            @if ($selectedProduct->options->count() > 0)
                @foreach($selectedProduct->options as $option)
                    <p class="fw-bold text-dark mb-2">{{ $option->name }}</p>
                    <div class="list-group mb-3">
                        @foreach($option->values as $value)
                        <label class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $value->value }}
                            </div>
                            <div>
                                <span class="text-muted small">
                                    @if($value->price_adjustment > 0)
                                        +Rp{{ number_format($value->price_adjustment, 0, ',', '.') }}
                                    @else
                                        Free
                                    @endif
                                </span>
                                <input 
                                    class="form-check-input ms-2"
                                    type="radio" 
                                    wire:model="selectedOptions.{{ $option->id }}" 
                                    wire:click="$refresh"
                                    value="{{ $value->id }}">
                            </div>
                        </label>
                        @endforeach
                    </div>
                @endforeach
            @else
                <p class="text-muted">No options available</p>
            @endif
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
    </div>
</div>
@endif

@if ($cartQuantity > 0)
<div class="cart-footer">
    <div class="eta">
        <p class="mb-0">Estimated Time 20 Minutes</p>
    </div>
    <a href="{{ route('cart.page') }}" class="cart-summary">
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
            type: 'loop',
            autoplay: true,
            perPage: 2,
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

