<nav class="sidebar shrunk d-flex flex-column flex-shrink-0 p-3">
      <div class="d-flex justify-content-between align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-4 fw-bold primer nav-brand">Qash</span>
        <button id="sidebarToggleMobile" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
      </div>

      <div class="sidebar-wrapper">
        <x-backoffice.sidebar-nav-section>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.dashboard')}}" class="bi bi-grid-1x2-fill me-2">Dashboard</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{ route('backoffice.attendance.index') }}" class="bi bi-building me-2" >Attendance</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>
        <x-backoffice.sidebar-nav-section section="Home">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.tables.index')}}" class="bi bi-grid-3x3-gap-fill me-2" >Dining Tables</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.tables.info')}}" class="bi bi-info-circle me-2" >Table Information</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{ route('backoffice.staff.view', ['staff' => request()->user()->id]) }}" class="bi bi-cash-coin me-2">Profile</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{ route('backoffice.settings.index') }}" class="bi bi-gear me-2" >Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.notification.index')}}" class="bi bi-bell me-2" >Notification</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Inventory">
          @can('inventory_products_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.product.index')}}" class="bi bi-list-task me-2">Product</x-backoffice.sidebar-nav-link>
          @endcan
          @can('inventory_category_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.category.index')}}" class="bi bi-cash-coin me-2">Category</x-backoffice.sidebar-nav-link>
          @endcan
          {{-- @can('inventory_special_type_view')
            <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Special Type</x-backoffice.sidebar-nav-link>
          @endcan --}}
          {{-- <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Deposit</x-backoffice.sidebar-nav-link> --}}
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Marketing">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Flash Deals</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >Dynamic Pop-up</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Custom Alert</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Email Templates</x-backoffice.sidebar-nav-link>
          {{-- <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Notification</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Coupon</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Custom Visitors</x-backoffice.sidebar-nav-link> --}}
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Promo">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Summary</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >Promo Code</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Discounts</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Reports">
          <x-backoffice.sidebar-nav-link href="{{ route('backoffice.reports.index') }}" class="bi bi-graph-up me-2">Reports</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="Peoples">
          <x-backoffice.sidebar-nav-link href="{{ route('customer.order') }}" class="bi bi-list-task me-2">Customer</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Customer Order</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        <x-backoffice.sidebar-nav-section section="POS & Orders">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">POS</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >POS Orders</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Table Orders</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.order.index')}}" class="bi bi-list-task me-2" >All Order</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        @can('hrm_view')
          <x-backoffice.sidebar-nav-section section="HRM">
            @can('hrm_employees_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.staff.index')}}" class="bi bi-list-task me-2" >All Staff</x-backoffice.sidebar-nav-link>
            @endcan
            @can('hrm_roles_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.roles.index')}}" class="bi bi-cash-coin me-2" >Staff Permission</x-backoffice.sidebar-nav-link>
            @endcan
            @can('hrm_shifts_view')
              <x-backoffice.sidebar-nav-link href="{{ route('backoffice.shift.index') }}" class="bi bi-building me-2" >Shift</x-backoffice.sidebar-nav-link>
            @endcan
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Cuti</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Leave</x-backoffice.sidebar-nav-link>
          </x-backoffice.sidebar-nav-section>
        @endcan

        @can('sales_view')
          <x-backoffice.sidebar-nav-section section="Sales">
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Sales</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Invoices</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Sales Return</x-backoffice.sidebar-nav-link>
          </x-backoffice.sidebar-nav-section>
        @endcan

        @can('kitchen_view')
          <x-backoffice.sidebar-nav-section section="Kitchen">
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.kitchen.index')}}" class="bi bi-fork-knife me-2" >K.D.S</x-backoffice.sidebar-nav-link>
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.kitchen.index')}}" class="bi bi-fork-knife me-2" >O.S.S</x-backoffice.sidebar-nav-link>
          </x-backoffice.sidebar-nav-section>
        @endcan
        
        <x-backoffice.sidebar-nav-section section="Content (CMS)">
          <x-backoffice.sidebar-nav-link href="{{route('backoffice.careers.index')}}" class="bi bi-list-task me-2">Career</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Select Homepage</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Homepage Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Font Family</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Authentication Layout</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Header Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Top Bar</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Footer Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Pages</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Appereance</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>

        {{-- <div class="section-title">Projects</div>
        <ul class="nav nav-pills flex-column mb-3">
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-list-task me-2"></i><span>Task</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-people me-2"></i><span>Team</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-bullseye me-2"></i><span>Goal</span></a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link"><i class="bi bi-ticket-perforated-fill me-2"></i><span>Ticket</span></a>
          </li>
        </ul> --}}
    </nav>
