{{-- resources/views/livewire/cart-page.blade.php --}}
<div class="container my-4 text-black" style="max-width: 480px;">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-3">
        <a href="{{ route('customer.order') }}" class="me-2 text-dark">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 ms-3">Take Away</h5>
        <button class="btn btn-sm btn-outline-secondary ms-auto">Change</button>
    </div>

    {{-- Pickup Location --}}
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted">Take your order at</small><br>
                <span class="fw-semibold">Pickup location not set yet</span>
            </div>
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>

    {{-- Your Order --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-semibold">Your Order</h6>
        <a href="{{ route('customer.order') }}" class="text-decoration-none small primer">Add more +</a>
    </div>

    {{-- Cart Items --}}
    @foreach($items as $item)
        <div class="card mb-3 border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-start">
                {{-- Product Image --}}
                @if($item->attributes->image)
                    <img src="{{ asset('storage/' . $item->attributes->image) }}" 
                         alt="{{ $item->name }}" 
                         class="rounded me-3" 
                         style="width: 64px; height: 64px; object-fit: cover;">
                @else
                    <img src="{{ asset('default.png') }}" 
                         alt="No Image" 
                         class="rounded me-3" 
                         style="width: 64px; height: 64px; object-fit: cover;">
                @endif

                <div class="flex-grow-1">
                    {{-- Name & Description --}}
                    <h6 class="mb-1 fw-semibold">{{ $item->name }}</h6>
                    {{-- Options --}}
                    @if($item->attributes->options)
                        <ul class="list-unstyled small text-muted mb-1">
                            @foreach($item->attributes->options as $option)
                                <li>{{ $option['value'] }} 
                                    @if($option['price_adjustment'] > 0)
                                        (+Rp.{{ number_format($option['price_adjustment'], 0) }})
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Quantity & Total --}}
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="d-flex align-items-center">
                            <button wire:click="decreaseQty({{ $item->id }})" 
                                    class="btn btn-sm btn-outline-secondary">-</button>
                            <span class="mx-2">{{ $item->quantity }}</span>
                            <button wire:click="increaseQty({{ $item->id }})" 
                                    class="btn btn-sm btn-outline-secondary">+</button>
                        </div>
                        <span class="fw-semibold">
                            Rp.{{ number_format($item->getPriceSum(), 0) }}
                        </span>
                    </div>
                </div>

                {{-- Remove Button --}}
                <button wire:click="removeItem({{ $item->id }})" 
                        class="btn btn-sm btn-link text-danger ms-2">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    @endforeach

    {{-- Discount --}}
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <span>Voucher</span>
            <a href="#" class="text-decoration-none small primer">Click to select voucher</a>
        </div>
    </div>

    {{-- Payment Method --}}
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <span>Choose Payment</span>
            <a href="#" class="text-decoration-none small primer">Choose your payment method</a>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="card mb-3 border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">Grand Subtotal</span>
                <span class="fw-semibold">Rp.{{ number_format($grandTotal, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Footer Pay Now --}}
    <div class="d-flex justify-content-between align-items-center p-3 border-top">
        <div>
            <small class="text-muted">Total Price</small><br>
            <span class="fw-bold fs-5">Rp.{{ number_format($grandTotal, 0) }}</span>
        </div>
        <button wire:click="checkout" class="btn btn-lg btn-dark rounded-pill px-4">Pay Now</button>
    </div>
</div>
