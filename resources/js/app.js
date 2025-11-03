import 'bootstrap';
import.meta.glob([
    '../fonts/**'
])

document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('logoLoaderOverlay');
  if (!overlay) return;

  // Hide overlay on initial load (in case server rendered it visible)
  overlay.classList.remove('is-active');

  // Show loader
  function showLoader() {
    overlay.setAttribute('aria-hidden', 'false');
    overlay.classList.add('is-active');
  }

  // Hide loader
  function hideLoader() {
    overlay.setAttribute('aria-hidden', 'true');
    overlay.classList.remove('is-active');
  }

  // Show loader on link clicks that will navigate (exclude anchors '#' and external links and targets _blank)
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a');
    if (!a) return;
    // ignore if link opens in new tab or has download attribute
    if (a.target === '_blank' || a.hasAttribute('download')) return;

    const href = a.getAttribute('href') || '';
    // ignore javascript: and anchors
    if (href.startsWith('javascript:') || href.startsWith('#') ) return;

    // relative/internal navigation: show loader
    showLoader();
    // allow the browser to navigate naturally
  }, { passive: true });

  // Show loader on form submit
  document.addEventListener('submit', function (e) {
    // forms that submit via JS (AJAX) should preventDefault, so this listener will only run on real submits
    showLoader();
  }, true);

  // Hide loader when page finishes loading (useful when coming back from bfcache or if loaded via ajax)
  window.addEventListener('pageshow', function (evt) {
    // pageshow fires after navigation back/forward too
    hideLoader();
  });

  // Optional: if you make fetch/ajax calls and want the loader:
  // export global functions:
  window.logoLoader = {
    show: showLoader,
    hide: hideLoader
  };
});
