<nav class="sidebar shrunk d-flex flex-column flex-shrink-0 p-3">
      <div class="d-flex justify-content-between align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-4 fw-bold primer nav-brand">Qash</span>
        <button id="sidebarToggleMobile" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
      </div>

      <div class="sidebar-wrapper">
        <x-backoffice.sidebar-nav-section>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.dashboard')}}" class="bi bi-grid-1x2-fill me-2">Dashboard</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>
        <x-backoffice.sidebar-nav-section section="Home">
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2">Profile</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Report</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.notification.index')}}" class="bi bi-bell me-2" >Notification</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Product">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.product.index')}}" class="bi bi-list-task me-2">Product</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.category.index')}}" class="bi bi-cash-coin me-2">Category</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Special Type</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Deposit</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="CRM">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.careers.index')}}" class="bi bi-list-task me-2">Career</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Marketing">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Voucher & Discount</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >Loyalty Point</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Special Type</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Deposit</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Staffs">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.staff.index')}}" class="bi bi-list-task me-2" >All Staffs</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.roles.index')}}" class="bi bi-cash-coin me-2" >Staff Permissions</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Sales">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.order.index')}}" class="bi bi-list-task me-2" >All Orders</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Kitchen">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.kitchen.index')}}" class="bi bi-fork-knife me-2" >Kitchen Orders</x-backoffice.sidebar-nav-link>
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
