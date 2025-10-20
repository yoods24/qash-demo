<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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

    @vite(['resources/css/backoffice.css', 'resources/js/backoffice.js', 'resources/css/app.css', 'resources/css/filament.css'])
    @livewireStyles
    @filamentStyles
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <x-backoffice.sidebar></x-backoffice.sidebar>
    <!-- Main content -->
    <div class="flex-grow-1 layout-main">
      <!-- Navbar -->
      <x-backoffice.navbar></x-backoffice.navbar>
      <!-- Dashboard content -->
      <div class="p-4 secondary-white main-component">
        @include('components.toast-delete')
        @livewire('notifications')
        {{$slot}}
      </div>
      <x-modal-delete />
    </div>
  </div>
  @stack('scripts')
  @vite('resources/js/app.js')
  @filamentScripts
  @livewireScripts
</body>
</html>

