<!-- components/loader.blade.php -->
<!-- Place this once (e.g., inside layouts/app.blade.php right after body tag) -->
<div id="logoLoaderOverlay" class="logo-loader-overlay" aria-hidden="true" role="status" aria-live="polite">
  <div class="logo-loader-card" aria-hidden="true">
    <!-- Use the spinning class by default; change to logo-translate to use the translate/fade animation -->
    <img src="{{ global_asset('storage/logos/Logogram-Orange.png') }}" alt="Loading" id="logoLoaderImg" class="logo-spin">
    <span class="visually-hidden">Loading…</span>
  </div>
</div>
