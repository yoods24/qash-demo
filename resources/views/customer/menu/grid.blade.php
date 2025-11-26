<x-customer.layout>
  @php
      /** @var \Illuminate\Support\Collection|\App\Models\Category[] $categories */
      $categories = $categories ?? collect();
  @endphp

  <section class="secondary-white">
    <div class="section-wrapper text-black">
      <div class="text-center mb-5">
        <p class="primer bold mb-2">OUR MENU</p>
        <h2 class="fw-bold mb-2">Grid Menu</h2>
        <p class="text-muted mb-0">Browse every section at a glance and find your next favorite dish.</p>
      </div>

      <div class="text-center my-3">
        <a href="{{ route('customer.order') }}">
          <button class="reservation-btn w-50">Order Now</button>
        </a>
      </div>

      @forelse ($categories as $category)
        <div class="menu-grid-section py-5">
          <div class="menu-grid-banner position-relative text-center mb-4 rounded-4 overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(0,0,0,0.65), rgba(0,0,0,0.4)); z-index: 1;"></div>
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('https://picsum.photos/seed/{{ $category->id }}/1600/600'); background-size: cover; background-position: center;"></div>
            <div class="position-relative py-5" style="z-index: 2;">
              <p class="text-uppercase text-white mb-1 small" style="letter-spacing: 0.12em;">Section</p>
              <h3 class="fw-bold text-white mb-0">{{ strtoupper($category->name) }}</h3>
            </div>
          </div>

          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @forelse ($category->products as $product)
              <div class="col">
                <div class="menu-grid-card h-100 p-3 p-lg-4 bg-white rounded-4 shadow-sm d-flex flex-column gap-2">
                  <div class="d-flex align-items-baseline gap-2">
                    <span class="fw-semibold">{{ $product->name }}</span>
                    <span class="flex-grow-1 border-bottom" style="border-bottom-style: dashed;"></span>
                    @if (! is_null($product->price))
                      <span class="fw-semibold text-end">
                        {{ number_format((float) $product->price, 0, ',', '.') }}
                      </span>
                    @endif
                  </div>
                  @if ($product->description)
                    <p class="text-muted small mb-0">{{ $product->description }}</p>
                  @endif
                  @if ($product->is_recommended ?? $product->featured ?? false)
                    <div class="mt-auto">
                      <span class="btn btn-outline-dark btn-sm rounded-pill px-3 py-1">
                        ★ Recommend
                      </span>
                    </div>
                  @endif
                </div>
              </div>
            @empty
              <div class="col">
                <div class="alert alert-light mb-0" role="alert">
                  Items are being prepared for this section. Please check back soon.
                </div>
              </div>
            @endforelse
          </div>
        </div>
      @empty
        <div class="alert alert-light mt-4" role="alert">
          Menu is coming soon. Please check back later.
        </div>
      @endforelse
    </div>
  </section>
</x-customer.layout>
