<x-customer.layout>
  <section class="secondary-white">
    <div class="section-wrapper no-p text-black menu-book-page-shell" style="min-height: auto;">
      <div class="text-center my-4">
        <p class="primer bold mb-1">OUR MENU</p>
        <h2 class="fw-bold mb-1">Our Menu</h2>
        <p class="text-muted">Discover the taste of real coffee.</p>
      </div>
      <div class="d-flex mb-4 w-50 justify-content-center container-center">
        <a href="{{ route('customer.order') }}" class="btn btn-main px-4 py-2 w-100">
          Order Now
        </a>
      </div>
      @livewire('book-menu')
    </div>
  </section>
</x-customer.layout>
