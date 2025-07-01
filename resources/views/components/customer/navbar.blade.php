<nav class="d-flex justify-content-between align-items-center">
    <div class="navbar-brand">
        <a class="h3 primer text-decoration-none bold" href="/">
            Qash
        </a>
    </div>
    <div>
        <x-customer.nav-link href="/" :active="request()->is('/')">Home</x-customer.nav-link>
        <x-customer.nav-link href="/menu" :active="request()->is('menu')">Menu</x-customer.nav-link>
        <x-customer.nav-link href="/brand"  :active="request()->is('brand')">Brand</x-customer.nav-link>
        <x-customer.nav-link href="/career"  :active="request()->is('career')">Careers</x-customer.nav-link>
        <x-customer.nav-link href="/events"  :active="request()->is('events')">Events</x-customer.-nav-link>
    </div>
</nav>