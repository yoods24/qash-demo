<nav class="sidebar d-flex flex-column flex-shrink-0 p-3">
      <div class="d-flex justify-content-between align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-4 fw-bold primer nav-brand">Qash</span>
        <button id="sidebarToggleMobile" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
      </div>

      <div class="sidebar-wrapper">
        <x-backoffice.sidebar-nav-section>
          <x-backoffice.sidebar-nav-link href="/backoffice" class="bi bi-grid-1x2-fill me-2" :active="request()->is('backoffice')" >Dashboard</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>
        <x-backoffice.sidebar-nav-section section="Home">
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2" :active="request()->is('profile')" >Profile</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" :active="request()->is('settings')" >Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" :active="request()->is('report')" >Report</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Product">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.product.index')}}" class="bi bi-list-task me-2" :active="request()->is('backoffice/product')" >Product</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.category.index')}}" class="bi bi-cash-coin me-2" :active="request()->is('backoffice/category')" >Category</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" :active="request()->is('settings')" >Special Type</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" :active="request()->is('report')" >Deposit</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="CRM">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.careers.index')}}" class="bi bi-list-task me-2" :active="request()->is('backoffice/career')" >Career</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Marketing">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2" :active="request()->is('Voucher & Discount')" >Voucher & Discount</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2" :active="request()->is('profile')" >Loyalty Point</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" :active="request()->is('settings')" >Special Type</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" :active="request()->is('report')" >Deposit</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Staffs">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.staff.index')}}" class="bi bi-list-task me-2" :active="request()->is('backoffice/staff')" >All Staffs</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.roles.index')}}" class="bi bi-cash-coin me-2" :active="request()->is('backoffice/roles')" >Staff Permissions</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <div class="section-title">Projects</div>
        <ul class="nav nav-pills flex-column mb-3">
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-list-task me-2"></i><span>Tasks</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-people me-2"></i><span>Teams</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-bullseye me-2"></i><span>Goals</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-ticket-perforated-fill me-2"></i><span>Tickets</span></a>
          </li>
        </ul>
    </nav>