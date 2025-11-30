// Global loading overlay controller
// Uses the overlay from resources/views/components/logo-loader.blade.php
(function () {
  const OVERLAY_ID = 'logoLoaderOverlay';
  const ACTIVE_CLASS = 'is-active';
  const BODY_BUSY_CLASS = 'qash-loading';
  const HIDE_DELAY_MS = 350; // allow navbar/sidebar transitions to finish
  const CLICK_SHOW_DELAY_MS = 0; // show immediately on user navigation

  let pendingNetwork = 0; // tracks non-Livewire requests we wrap
  let hideTimer = null;

  const $ = (id) => document.getElementById(id);

  const getOverlay = () => $(OVERLAY_ID);

  const showOverlay = () => {
    const el = getOverlay();
    if (!el) return;
    if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
    if (!el.classList.contains(ACTIVE_CLASS)) {
      el.classList.add(ACTIVE_CLASS);
      document.body.classList.add(BODY_BUSY_CLASS);
    }
  };

  const hideOverlay = () => {
    const el = getOverlay();
    if (!el) return;
    if (el.classList.contains(ACTIVE_CLASS)) {
      el.classList.remove(ACTIVE_CLASS);
      document.body.classList.remove(BODY_BUSY_CLASS);
    }
  };

  const hideOverlayDeferred = (delay = HIDE_DELAY_MS) => {
    if (hideTimer) clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
      hideTimer = null;
      // Only hide when no wrapped requests are pending
      if (pendingNetwork <= 0) hideOverlay();
    }, delay);
  };

  // Helpers to identify Livewire requests we should ignore
  const isLivewireRequest = (url = '') => {
    try {
      const u = typeof url === 'string' ? url : String(url);
      return u.includes('/livewire/update') || u.includes('/livewire/message/');
    } catch { return false; }
  };

  // Skip Livewire polling components (wire:poll, wire:poll.visible, etc.)
  const isPollingComponent = (component) => {
    try {
      const root = component?.el || component?.root || component?.$el;
      if (!root) return false;
      if (root.hasAttribute?.('wire:poll')) return true;
      return !!root.querySelector?.('[wire\\:poll],[wire\\:poll\\.visible],[wire\\:poll\\.keep-alive]');
    } catch { return false; }
  };

  // Wrap window.fetch to track generic network activity
  const patchFetch = () => {
    if (!window.fetch) return;
    const origFetch = window.fetch.bind(window);
    window.fetch = function (input, init) {
      const url = typeof input === 'string' ? input : (input?.url || '');
      const method = (init?.method || 'GET').toUpperCase();

      // Ignore Livewire update/message endpoints entirely (handled via hooks below)
      const ignore = isLivewireRequest(url);
      if (!ignore) {
        pendingNetwork++;
        showOverlay();
      }

      const finalize = () => {
        if (!ignore) {
          pendingNetwork = Math.max(0, pendingNetwork - 1);
          if (pendingNetwork === 0) hideOverlayDeferred();
        }
      };

      try {
        const p = origFetch(input, init);
        // Ensure we always finalize regardless of success/failure
        return p.finally(finalize);
      } catch (e) {
        finalize();
        throw e;
      }
    };
  };

  // Wrap XMLHttpRequest to track generic network activity (for libs that still use XHR)
  const patchXHR = () => {
    if (!('XMLHttpRequest' in window)) return;
    const OrigXHR = window.XMLHttpRequest;
    function WrappedXHR() {
      const xhr = new OrigXHR();
      let _url = '';
      let _method = 'GET';
      let ignored = false;

      const finalize = () => {
        if (!ignored) {
          pendingNetwork = Math.max(0, pendingNetwork - 1);
          if (pendingNetwork === 0) hideOverlayDeferred();
        }
      };

      const origOpen = xhr.open;
      xhr.open = function (method, url, ...rest) {
        _method = (method || 'GET').toUpperCase();
        _url = url || '';
        ignored = isLivewireRequest(_url);
        return origOpen.apply(xhr, [method, url, ...rest]);
      };

      const origSend = xhr.send;
      xhr.send = function (...args) {
        if (!ignored) {
          pendingNetwork++;
          showOverlay();
        }
        xhr.addEventListener('loadend', finalize, { once: true });
        try { return origSend.apply(xhr, args); } catch (e) { finalize(); throw e; }
      };

      return xhr;
    }
    window.XMLHttpRequest = WrappedXHR;
  };

  // Livewire integration: show for user-driven messages, skip polls
  const initLivewireHooks = () => {
    const LW = window.Livewire;
    if (!LW || typeof LW.hook !== 'function') return;

    // message.sent fires before a request is made
    LW.hook('message.sent', (message, component) => {
      if (isPollingComponent(component)) return; // skip polling updates
      showOverlay();
    });

    const finishLivewireMessage = (component) => {
      if (isPollingComponent(component)) return;
      hideOverlay();
    };

    // message.processed fires after DOM updates
    LW.hook('message.processed', (message, component) => {
      finishLivewireMessage(component);
    });

    // message.failed triggers when the request errors before processing
    LW.hook('message.failed', (message, component) => {
      finishLivewireMessage(component);
    });

    // message.received still fires before DOM diffing; use as a fallback to prevent locking
    LW.hook('message.received', (message, component) => {
      // If Livewire short-circuits (redirect, download, etc.) ensure we hide the loader.
      setTimeout(() => finishLivewireMessage(component), 0);
    });

    // Allow Livewire components to explicitly hide overlay
    try {
      LW.on('overlay-hide', () => hideOverlay());
    } catch (e) { /* no-op */ }
  };

  // Navigation triggers: links, form submits, and beforeunload
  const initNavigationTriggers = () => {
    // Link clicks
    document.addEventListener('click', (e) => {
      const a = e.target?.closest?.('a[href]');
      if (!a) return;
      const href = a.getAttribute('href') || '';
      const target = a.getAttribute('target');
      const download = a.hasAttribute('download');
      const isVoid = href.startsWith('#') || href.startsWith('javascript:');
      if (download || isVoid || (target && target !== '_self')) return;
      // internal navigation: show immediately
      setTimeout(showOverlay, CLICK_SHOW_DELAY_MS);
    }, true);

    // Form submits
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!form) return;
      // If Livewire intercepts the form (has wire:submit), it will also trigger hooks
      // Showing overlay here is still fine for instant feedback
      setTimeout(showOverlay, CLICK_SHOW_DELAY_MS);
    }, true);

    // Page unload (hard navigations)
    window.addEventListener('beforeunload', () => {
      // Attempt to show overlay while leaving
      showOverlay();
    });
  };

  // Initialize once DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    patchFetch();
    patchXHR();
    initNavigationTriggers();
    // Livewire may load later; hook when ready
    if (window.Livewire) initLivewireHooks();
    document.addEventListener('livewire:load', initLivewireHooks, { once: true });

    // If overlay happens to be visible, hide it after a short delay
    hideOverlayDeferred();
  });
})();

