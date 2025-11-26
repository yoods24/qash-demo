<x-customer.layout>
  @php
      /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\Order[]|null $orders */
      $orders = $orders ?? null;
      $name = $name ?? old('name');
      $email = $email ?? old('email');
  @endphp

  <section class="secondary-white">
    <div class="section-wrapper text-black">
      <div class="text-center mb-4">
        <p class="primer bold mb-1">ORDER LOOKUP</p>
        <h2 class="fw-bold mb-2">Check My Orders</h2>
        <p class="text-muted mb-0">Enter your name and email to see your recent orders for this location.</p>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body p-4">
              <form method="POST" action="{{ route('customer.orders.search') }}" class="d-flex flex-column gap-3">
                @csrf
                <div>
                  <label class="form-label fw-semibold" for="customer-name">Name</label>
                  <input
                    id="customer-name"
                    type="text"
                    name="name"
                    value="{{ old('name', $name) }}"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Enter your name"
                    required
                  >
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div>
                  <label class="form-label fw-semibold" for="customer-email">Email</label>
                  <input
                    id="customer-email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@example.com"
                    required
                  >
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn btn-main px-4">
                    Check Orders
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      @if (! is_null($orders))
        <div class="mt-5">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="fw-semibold mb-0">Results</h5>
            @if(method_exists($orders, 'total'))
              <span class="text-muted small">Found {{ $orders->total() }} order{{ $orders->total() === 1 ? '' : 's' }}</span>
            @endif
          </div>

          @if ($orders->count() > 0)
            <div class="row row-cols-1 row-cols-md-2 g-3">
              @foreach ($orders as $order)
                <div class="col">
                  <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column gap-2">
                      <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                          <p class="text-uppercase small text-muted mb-1">Order</p>
                          <h6 class="fw-bold mb-0">#{{ $order->reference_no ?? $order->id }}</h6>
                        </div>
                        <span class="badge bg-light text-dark text-uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted small">Placed</span>
                        <span class="small fw-semibold">{{ optional($order->created_at)->format('M d, Y H:i') }}</span>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted small">Total</span>
                        <span class="small fw-semibold">IDR {{ number_format((float) $order->grand_total, 0, ',', '.') }}</span>
                      </div>
                      <div class="mt-auto d-flex justify-content-end">
                        <a href="{{ route('order.track', $order) }}" class="btn btn-outline-main btn-sm px-3">
                          Track Order
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="mt-3">
              {{ $orders->withQueryString()->links() }}
            </div>
          @else
            <div class="alert alert-light mt-3 mb-0" role="alert">
              No orders found for this name/email.
            </div>
          @endif
        </div>
      @endif
    </div>
  </section>
</x-customer.layout>
