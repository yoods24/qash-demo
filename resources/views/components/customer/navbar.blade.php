<nav class="customer-navbar d-flex justify-content-between align-items-center text-center">
    <div class="navbar-brand">
        <a class="h3 primer text-decoration-none bold" href="{{ route('customer.home') }}">
            {{ tenant('id') }}
        </a>
    </div>
    <div>
    <button
        id="hamburgToggle"
        type="button"
        class="btn btn-link text-white d-md-none p-0 ms-3"
        aria-label="Toggle navigation"
        aria-expanded="false"
        aria-controls="customerNavMenu"
    >
        <i class="bi bi-list fs-2"></i>
    </button>
    </div>
    <div id="customerNavMenu" class="customer-nav-links d-flex align-items-start gap-4">
        <x-customer.nav-link href="{{ route('customer.home') }}">Home</x-customer.nav-link>
        <x-customer.nav-link href="{{ route('customer.menu.layout') }}">Menu</x-customer.nav-link>
        <x-customer.nav-link href="{{ route('customer.career.index') }}">Careers</x-customer.nav-link>
        <x-customer.nav-link href="{{ route('customer.events.index') }}">Events</x-customer.nav-link>
    </div>
</nav>
