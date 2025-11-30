<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Login - Qash</title>
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
    {{--loading icon --}}
    <link rel="icon" href="{{ global_asset('storage/logos/Qash_single_logogram.png') }}" type="image/png">

    @vite([
      'resources/css/backoffice.css',
      'resources/js/backoffice.js',
      'resources/js/logo-loader.js',
      'resources/css/app.css',
      'resources/js/app.js',
      // Keep Bootstrap collapse semantics authoritative
      'resources/css/overrides.css',
    ])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-main">
  @include('components.logo-loader')

  <main class="d-flex align-items-center justify-content-center min-vh-100">
    {{ $slot }}
  </main>

  @stack('scripts')
</body>
</html>