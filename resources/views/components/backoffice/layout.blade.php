<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Dashboard - Qash</title>
    <script>
      // Initialize theme early to avoid flash
      (function() {
        try {
          var key = 'qash:theme';
          var saved = localStorage.getItem(key);
          var theme = saved || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
          document.documentElement.dataset.theme = theme;
          document.documentElement.classList.toggle('dark', theme === 'dark');
        } catch (e) { /* no-op */ }
      })();
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    {{-- map for geolocation --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

    @vite([
      'resources/css/backoffice.css',
      'resources/js/backoffice.js',
      'resources/css/app.css',
      'resources/css/filament.css',
      // Keep Bootstrap collapse semantics authoritative
      'resources/css/overrides.css',
    ])
    @livewireStyles
    @filamentStyles
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
  @include('components.logo-loader')

  <div class="d-flex">
    <!-- Sidebar -->
    <x-backoffice.sidebar></x-backoffice.sidebar>
    <!-- Main content -->
    <div class="flex-grow-1 layout-main">
      <!-- Navbar -->
      <x-backoffice.navbar></x-backoffice.navbar>
      <!-- Dashboard content -->
      <div class="p-4 bg-main">
        @include('components.toast-delete')
        @livewire('notifications')
        {{$slot}}
      </div>
      <x-modal.delete />
      <x-modal.cancel />
    </div>
  </div>
  @stack('scripts')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  @filamentScripts
  @livewireScripts
</body>
</html>
