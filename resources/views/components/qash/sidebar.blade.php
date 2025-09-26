<nav class="sidebar shrunk d-flex flex-column flex-shrink-0 p-3">
      <div class="d-flex justify-content-between align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-4 fw-bold primer nav-brand">Qash</span>
        <button id="sidebarToggleMobile" title="Toggle Sidebar"><i class="bi bi-list"></i></button>
      </div>

      <div class="sidebar-wrapper">
        <x-backoffice.sidebar-nav-section>
          <x-backoffice.sidebar-nav-link href="/backoffice" class="bi bi-grid-1x2-fill me-2">Dashboard</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>
        <x-backoffice.sidebar-nav-section section="Home">
          <x-backoffice.sidebar-nav-link class="bi bi-cash-coin me-2">Profile</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-gear me-2">Settings</x-backoffice.sidebar-nav-link>
          <x-backoffice.sidebar-nav-link class="bi bi-building me-2">Report</x-backoffice.sidebar-nav-link>
        </x-backoffice.sidebar-nav-section>
    </nav>