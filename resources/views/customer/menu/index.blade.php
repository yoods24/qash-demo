<x-customer.layout>
  <section class="secondary-white">
    <div class="section-wrapper no-p text-black menu-book-page-shell" style="min-height: auto;">
      <div class="text-center my-4">
        <p class="primer bold mb-1">OUR MENU</p>
        <h2 class="fw-bold mb-1">Our Menu</h2>
        <p class="text-muted">Discover the taste of real coffee.</p>
      </div>
      <div class="text-center my-3">
        <a href="{{ route('customer.order') }}">
          <button class="reservation-btn w-50">Order Now</button>
        </a>
      </div>
      @livewire('book-menu')
    </div>
  </section>
</x-customer.layout>
