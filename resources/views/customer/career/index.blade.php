<x-customer.layout>
  <section class="secondary-white">
    <div class="section-wrapper text-black" style="min-height: auto;">
      <div class="text-center mb-4">
        <p class="primer bold mb-1">OPEN POSITIONS</p>
        <h2 class="fw-bold mb-1">Find your perfect role</h2>
        <p class="text-muted mb-0">Join our team and start your journey with us.</p>
      </div>

      <div class="container px-0">
        <div class="row">
          @foreach ($careers as $career)
            <x-customer.career-card-wide :$career />
          @endforeach
        </div>

        <div class="mt-4">
          {{ $careers->links() }}
        </div>
      </div>
    </div>
  </section>
</x-customer.layout>
