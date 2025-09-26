<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Qash</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    
    @vite(['resources/css/backoffice.css', 'resources/js/backoffice.js', 'resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <x-qash.sidebar></x-qash.sidebar>
    <!-- Main content -->
    <div class="flex-grow-1 layout-main">
      <!-- Navbar -->
      <x-qash.navbar></x-qash.navbar>
      <!-- Dashboard content -->
      <div class="p-4 secondary-white">
        @include('components.toast-delete')
        {{$slot}}
      </div>
      <x-modal-delete />
    </div>
  </div>
  @stack('scripts')
</body>
</html>
