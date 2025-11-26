<nav class="sidebar shrunk d-flex flex-column flex-shrink-0 p-3" data-lenis-prevent>
      <div class="brand-wrap d-flex align-items-center justify-content-between justify-content-md-center text-white text-decoration-none">
          <!-- Expanded: full logo | Shrunk: compact mark -->
          <img onclick="window.location='{{ route('backoffice.dashboard') }}'" src="{{ global_asset('storage/logos/Main Logo-Orange.png') }}" alt="Qash" class="brand-img brand-compact" />
          <img onclick="window.location='{{ route('backoffice.dashboard') }}'" src="{{ global_asset('storage/logos/Logotype-Orange.png') }}" alt="Qash" class="brand-img brand-full" />
        <button id="sidebarToggleMobile" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
      </div>

      @php
        $inventoryPermissions = ['inventory_products_view', 'inventory_category_view', 'inventory_special_type_view', 'inventory_sub_category_view'];
        $promoPermissions = ['promo_view', 'promo_summary_view', 'promo_code_view', 'promo_discounts_view'];
        $reportPermissions = ['reports_view', 'reports_sales_view', 'reports_inventory_view', 'reports_invoice_view', 'reports_customer_view', 'reports_product_view', 'reports_profit_loss_view', 'reports_annual_view', 'reports_kitchen_view'];
        $peoplePermissions = ['peoples_customers_view', 'peoples_view'];
        $posPermissions = ['pos_view', 'pos_orders_view', 'sales_orders_view'];
        $tablePermissions = ['tables_view', 'pos_table_orders_view', 'table_information_view', 'table_plan_view'];
        $contentPermissions = [
            'content_view',
            'content_homepage_settings_view',
            'content_select_homepage_view',
            'content_font_family_view',
            'content_auth_layout_settings_view',
            'content_header_settings_view',
            'content_select_header_view',
            'content_top_bar_view',
            'content_footer_settings_view',
            'content_pages_view',
            'content_appearance_view',
            'content_events_view',
            'content_careers_view',
            'content_about_view',
            'content_brand_information_view',
        ];
      @endphp

      <div class="sidebar-wrapper">
        <x-backoffice.sidebar-nav-section>
          @can('dashboard_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.dashboard')}}" class="bi bi-grid-1x2-fill me-2">Dashboard</x-backoffice.sidebar-nav-link>
          @endcan
          @can('attendance_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.attendance.index') }}" class="bi bi-building me-2" >Attendance</x-backoffice.sidebar-nav-link>
          @endcan
        </x-backoffice.sidebar-nav-section>
        <x-backoffice.sidebar-nav-section section="Home">
          @can('profile_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.staff.view', ['staff' => request()->user()->id]) }}" class="bi bi-cash-coin me-2">Profile</x-backoffice.sidebar-nav-link>
          @endcan
          @can('settings_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.settings.index') }}" class="bi bi-gear me-2" >Settings</x-backoffice.sidebar-nav-link>
          @endcan
          @can('notification_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.notification.index')}}" class="bi bi-bell me-2" >Notification</x-backoffice.sidebar-nav-link>
          @endcan
          @can('taxes_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.taxes.index')}}" class="bi bi-cash me-2" >Taxes</x-backoffice.sidebar-nav-link>
          @endcan
        </x-backoffice.sidebar-nav-section>

        @canany($tablePermissions)
          <x-backoffice.sidebar-nav-section section="Table">
            @can('pos_table_orders_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.tables.index')}}" class="bi bi-grid-3x3-gap-fill me-2" >Dining Tables</x-backoffice.sidebar-nav-link>
            @endcan
            @can('table_information_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.tables.info')}}" class="bi bi-info-circle me-2" >Table Information</x-backoffice.sidebar-nav-link>
            @endcan
            @can('table_plan_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.tables.plan')}}" class="bi bi-info-circle me-2" >Table Plan</x-backoffice.sidebar-nav-link>
            @endcan
          </x-backoffice.sidebar-nav-section>
        @endcanany

        @canany($inventoryPermissions)
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
        @endcanany

        {{-- <x-backoffice.sidebar-nav-section section="Marketing">
          <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Flash Deals</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >Dynamic Pop-up</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Custom Alert</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Email Templates</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Notification</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Coupon</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Custom Visitors</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section> --}}

        @canany($promoPermissions)
          <x-backoffice.sidebar-nav-section section="Promo">
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Summary</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >Promo Code</x-backoffice.sidebar-nav-link>
            @can('promo_discounts_view')
              <x-backoffice.sidebar-nav-link href="{{ route('backoffice.discounts.index') }}" class="bi bi-gear me-2" >Discounts</x-backoffice.sidebar-nav-link>
            @endcan
          </x-backoffice.sidebar-nav-section>
        @endcanany

        @canany($reportPermissions)
          <x-backoffice.sidebar-nav-section section="Reports">
            @can('reports_view')
              <x-backoffice.sidebar-nav-link href="{{ route('backoffice.reports.index') }}" class="bi bi-graph-up me-2">Reports</x-backoffice.sidebar-nav-link>
            @endcan
          </x-backoffice.sidebar-nav-section>
        @endcanany

        @canany($peoplePermissions)
          <x-backoffice.sidebar-nav-section section="Peoples">
          @can('peoples_customers_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.customers.index') }}" class="bi bi-people me-2">Customers</x-backoffice.sidebar-nav-link>
          @endcan
          @can('peoples_view')
            <x-backoffice.sidebar-nav-link href="{{ route('customer.order') }}" class="bi bi-list-task me-2">Customer Order</x-backoffice.sidebar-nav-link>
          @endcan
        </x-backoffice.sidebar-nav-section>
        @endcanany

        @canany($posPermissions)
          <x-backoffice.sidebar-nav-section section="POS & Orders">
            @can('pos_view')
              <x-backoffice.sidebar-nav-link href="{{ route('backoffice.pos.index') }}" class="bi bi-list-task me-2">POS</x-backoffice.sidebar-nav-link>
            @endcan
            @can('pos_orders_view')
              <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2"  >POS Orders</x-backoffice.sidebar-nav-link>
            @endcan
            @can('pos_table_orders_view')
              <x-backoffice.sidebar-nav-link class="bi bi-gear me-2" >Table Orders</x-backoffice.sidebar-nav-link>
            @endcan
            @can('sales_orders_view')
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.order.index')}}" class="bi bi-list-task me-2" >All Order</x-backoffice.sidebar-nav-link>
            @endcan
          </x-backoffice.sidebar-nav-section>
        @endcanany

        @can('hrm_view')
          <x-backoffice.sidebar-nav-section section="HRM">
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.attendance.staff') }}" class="bi bi-people-fill me-2">Staff Attendance</x-backoffice.sidebar-nav-link>
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

        {{-- @can('sales_view')
          <x-backoffice.sidebar-nav-section section="Sales">
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Sales</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Invoices</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-building me-2" >Sales Return</x-backoffice.sidebar-nav-link>
          </x-backoffice.sidebar-nav-section>
        @endcan --}}

        @can('kitchen_view')
          @php
              $kdsPendingCount = \App\Models\Order::query()
                  ->whereIn('status', ['confirmed', 'preparing'])
                  ->whereIn('order_type', ['dine-in', 'takeaway'])
                  ->count();
          @endphp
          <x-backoffice.sidebar-nav-section section="Kitchen">
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.kitchen.index')}}" class="bi bi-fork-knife me-2" >
                <span class="d-inline-flex align-items-center gap-2">
                  K.D.S
                  @if($kdsPendingCount > 0)
                    <span class="badge rounded-pill bg-danger-subtle text-danger fw-semibold ms-1">{{ $kdsPendingCount }}</span>
                  @endif
                </span>
              </x-backoffice.sidebar-nav-link>
              <x-backoffice.sidebar-nav-link href="{{route('backoffice.kitchen.index')}}" class="bi bi-fork-knife me-2" >O.S.S</x-backoffice.sidebar-nav-link>
          </x-backoffice.sidebar-nav-section>
        @endcan
        
        @canany($contentPermissions)
          <x-backoffice.sidebar-nav-section section="Content (CMS)">
          <x-backoffice.sidebar-nav-link href="{{ route('customer.home') }}" class="bi bi-house me-2">Home Page</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link href="{{ route('customer.order') }}" class="bi bi-wallet me-2">Order Page</x-backoffice.sidebar-nav-link>
          @can('content_events_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.events.index') }}" class="bi bi-calendar-event me-2 heroicon heroicon-o-calendar">Events</x-backoffice.sidebar-nav-link>
          @endcan
          @can('content_careers_view')
            <x-backoffice.sidebar-nav-link href="{{route('backoffice.careers.index')}}" class="bi bi-list-task me-2">Career</x-backoffice.sidebar-nav-link>
          @endcan
          @can('content_about_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.about.index') }}"  class="bi bi-list-task me-2">About Homepage</x-backoffice.sidebar-nav-link>
          @endcan
          @can('content_brand_information_view')
            <x-backoffice.sidebar-nav-link href="{{ route('backoffice.brand-information.index') }}"  class="bi bi-type-h1 me-2">Brand Information</x-backoffice.sidebar-nav-link>
          @endcan
            {{-- <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Select Homepage</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Homepage Settings</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Font Family</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Authentication Layout</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Header Settings</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Top Bar</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Footer Settings</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Pages</x-backoffice.sidebar-nav-link>
            <x-backoffice.sidebar-nav-link class="bi bi-list-task me-2">Appereance</x-backoffice.sidebar-nav-link> --}}
          </x-backoffice.sidebar-nav-section>
        @endcanany

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
