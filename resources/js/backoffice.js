import * as bootstrap from 'bootstrap';
// Make Bootstrap JS available globally so inline Blade scripts can use it
window.bootstrap = bootstrap;
  
  
  // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //   return new bootstrap.Tooltip(tooltipTriggerEl);
    // });



    // Sidebar toggle
document.addEventListener("DOMContentLoaded", function () {
        // settings sidebar
          (function(){
        // ensure chevrons visually reflect expanded state
        document.querySelectorAll('#settingsNav .settings-toggle').forEach(btn => {
          const setState = () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.classList.toggle('collapsed', !expanded);
          };
          btn.addEventListener('click', () => setTimeout(setState, 0));
          setState();
        });

        // Auto-open the correct group when jumping to a tab hash
        const hash = window.location.hash;
        if (hash === '#att-pane' || hash === '#geo-pane') {
          const el = document.getElementById('settings-app');
          if (el && !el.classList.contains('show')) {
            const collapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
            collapse.show();
          }
        }
      })();
            // Navigation bar toggle
    (function() {
        const nav = document.querySelector('.navbar.navbar-custom');
        const btn = document.getElementById('toggleNavigationBar');
        const icon = btn ? btn.querySelector('i') : null;
        const key = 'qash:navbar:visible';
        const handleContainer = btn ? btn.closest('.toggle-nav-container') : null;
        const MOBILE_MAX = 768;
        const isMobile = () => window.innerWidth <= MOBILE_MAX;

        if (!nav || !btn) return;

        // Cache and maintain a non-zero base height for the navbar
        const setCssVars = () => {
            const isHidden = nav.classList.contains('hide');
            let base = parseInt(nav.dataset.navBaseHeight || '0', 10) || 0;
            if (!isHidden) {
                const cur = Math.round(nav.getBoundingClientRect().height);
                if (cur > 0) {
                    base = cur;
                    nav.dataset.navBaseHeight = String(base);
                }
            }
            if (!base) base = 56; // fallback
            document.documentElement.style.setProperty('--nav-height', base + 'px');
        };

        const setIcon = (visible) => {
            if (!icon) return;
            icon.className = 'bi p-1';
            icon.classList.add(visible ? 'bi-arrow-up-circle-fill' : 'bi-arrow-down-circle-fill');
        };

        const apply = (visible) => {
            nav.classList.toggle('hide', !visible);
            setIcon(visible);
            try { localStorage.setItem(key, String(visible)); } catch (e) {}
            btn.setAttribute('aria-expanded', String(visible));
            btn.setAttribute('aria-label', visible ? 'Hide navigation bar' : 'Show navigation bar');
            // After state change, ensure CSS var uses the last known non-zero height
            setTimeout(setCssVars, 0);
        };

        const getStoredVisible = () => {
            try {
                const s = localStorage.getItem(key);
                return s === null ? null : s === 'true';
            } catch (e) { return null; }
        };

        const updateForViewport = () => {
            if (isMobile()) {
                // On mobile: always show navbar and hide the toggle button
                nav.classList.remove('hide');
                setIcon(true);
                if (handleContainer) handleContainer.style.display = 'none';
                btn.setAttribute('aria-expanded', 'true');
                btn.setAttribute('aria-hidden', 'true');
                setTimeout(setCssVars, 0);
            } else {
                // On desktop: show the handle and apply stored visibility
                if (handleContainer) handleContainer.style.display = '';
                btn.removeAttribute('aria-hidden');
                const stored = getStoredVisible();
                const initialVisible = stored !== null ? stored : !nav.classList.contains('hide');
                apply(initialVisible);
            }
        };

        // Initialize for current viewport
        updateForViewport();

        // Toggle on click (desktop only)
        btn.addEventListener('click', (e) => {
            if (isMobile()) return; // disabled on mobile
            e.preventDefault();
            const visible = nav.classList.contains('hide');
            apply(visible);
        });

        // Recalculate on resize and adapt behavior for viewport
        window.addEventListener('resize', () => {
            updateForViewport();
        });
    })();

    // Global helper: navigate back without persisting changes
    window.redirectToPrevious = function redirectToPrevious() {
        try {
            if (window.history && window.history.length > 1) {
                window.history.back();
                return;
            }
            if (document.referrer) {
                window.location.href = document.referrer;
                return;
            }
            window.location.href = window.location.origin || '/';
        } catch (e) {
            window.location.href = '/';
        }
    };
    // Theme toggle
    (function() {
        const key = 'qash:theme';
        const btn = document.getElementById('themeToggle');
        const icon = btn ? btn.querySelector('[data-theme-icon]') : null;

        const setIcon = (theme) => {
            if (!icon) return;
            icon.className = 'bi';
            icon.classList.add(theme === 'dark' ? 'bi-sun' : 'bi-moon-stars');
        };

        const apply = (theme) => {
            // Persist string value for our own CSS and logic
            document.documentElement.dataset.theme = theme;
            // Also toggle Tailwind/Filament dark mode class
            document.documentElement.classList.toggle('dark', theme === 'dark');
            try { localStorage.setItem(key, theme); } catch (e) {}
            setIcon(theme);
        };

        // Initialize icon from current theme
        const current = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
        setIcon(current);

        if (btn) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const next = (document.documentElement.dataset.theme === 'dark') ? 'light' : 'dark';
                apply(next);
            });
        }
    })();
    // Fullscreen toggle
    (function() {
        const btn = document.getElementById('fullscreenToggle');
        if (!btn) return;
        const icon = btn.querySelector('[data-fullscreen-icon]');

        const doc = document;
        const root = document.documentElement;

        const supports = !!(root.requestFullscreen || root.webkitRequestFullscreen || root.msRequestFullscreen);
        if (!supports) {
            btn.style.display = 'none';
            return;
        }

        const isFs = () => !!(doc.fullscreenElement || doc.webkitFullscreenElement || doc.msFullscreenElement);

        const setIcon = () => {
            if (!icon) return;
            icon.className = 'bi';
            icon.classList.add(isFs() ? 'bi-fullscreen-exit' : 'bi-fullscreen');
        };

        const enter = () => {
            if (root.requestFullscreen) return root.requestFullscreen();
            if (root.webkitRequestFullscreen) return root.webkitRequestFullscreen();
            if (root.msRequestFullscreen) return root.msRequestFullscreen();
        };

        const exit = () => {
            if (doc.exitFullscreen) return doc.exitFullscreen();
            if (doc.webkitExitFullscreen) return doc.webkitExitFullscreen();
            if (doc.msExitFullscreen) return doc.msExitFullscreen();
        };

        setIcon();

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isFs()) {
                exit();
            } else {
                enter();
            }
        });

        ['fullscreenchange', 'webkitfullscreenchange', 'MSFullscreenChange', 'msfullscreenchange']
            .forEach(evt => doc.addEventListener(evt, setIcon));
    })();

    const sidebar = document.querySelector('.sidebar');
    const desktopToggle = document.querySelector('#sidebarToggleDesktop');
    const mobileToggle = document.querySelector('#sidebarToggleMobile');
    const SIDEBAR_KEY = 'qash:sidebar:shrunk';

    // Helper: whether viewport is mobile
    const isMobile = () => window.innerWidth <= 768;

    // Apply initial persisted state
    if (sidebar) {
        let stored = null;
        try { stored = localStorage.getItem(SIDEBAR_KEY); } catch (e) { stored = null; }
        const initialShrunk = stored !== null ? stored === 'true' : sidebar.classList.contains('shrunk');

        if (isMobile()) {
            // On mobile, ensure shrunk doesn't block overlay behavior
            sidebar.classList.remove('shrunk');
        } else {
            sidebar.classList.toggle('shrunk', initialShrunk);
        }
    }

    const persistShrunk = () => {
        if (!sidebar) return;
        try { localStorage.setItem(SIDEBAR_KEY, String(sidebar.classList.contains('shrunk'))); } catch (e) {}
    };

    // Desktop nav toggle: shrink on desktop, open on mobile
    if (desktopToggle) {
        desktopToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (!sidebar) return;
            if (isMobile()) {
                sidebar.classList.toggle('open');
                sidebar.classList.remove('shrunk'); // avoid CSS clamp on mobile
                document.body.classList.toggle('sidebar-open', sidebar.classList.contains('open'));
            } else {
                sidebar.classList.toggle('shrunk');
                persistShrunk();
            }
        });
    }

    // In-sidebar mobile toggle: close/open overlay
    if (mobileToggle) {
        mobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (!sidebar) return;
            sidebar.classList.toggle('open');
            sidebar.classList.remove('shrunk'); // ensure visible when open on mobile
            document.body.classList.toggle('sidebar-open', sidebar.classList.contains('open'));
        });
    }

    // Close sidebar when resizing up to desktop and re-apply persisted state
    window.addEventListener('resize', () => {
        if (!sidebar) return;
        if (!isMobile()) {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
            // Re-apply persisted shrunk state on desktop
            let stored = null;
            try { stored = localStorage.getItem(SIDEBAR_KEY); } catch (e) { stored = null; }
            const shrunk = stored === 'true';
            sidebar.classList.toggle('shrunk', shrunk);
        } else {
            // On entering mobile, remove shrunk so overlay can show
            sidebar.classList.remove('shrunk');
        }
    });

    // Click outside to close on mobile
    document.addEventListener('click', (e) => {
        if (!isMobile()) return;
        if (!sidebar) return;
        if (!sidebar.classList.contains('open')) return;
        const insideSidebar = sidebar.contains(e.target);
        const isToggle = e.target.closest('#sidebarToggleDesktop') || e.target.closest('#sidebarToggleMobile');
        if (!insideSidebar && !isToggle) {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
        }
    });

    // (reverted) removed kitchen display layout toggle and list icon rotation handlers

});

document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        confirmModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const itemId = button?.getAttribute('data-id');
            const itemTitle = button?.getAttribute('data-title');
            const deleteUrl = button?.getAttribute('data-url');

            const titleElement = confirmModal.querySelector('#modalItemTitle');
            const idElement = confirmModal.querySelector('#modalItemId');
            const form = confirmModal.querySelector('#deleteForm');

            if (titleElement) titleElement.textContent = `"${itemTitle}"`;
            if (idElement) idElement.textContent = `"${itemId}"`;
            if (form && deleteUrl) form.action = deleteUrl;
        });
    }

    const discountUpdateModal = document.getElementById('discountUpdateModal');
    if (discountUpdateModal) {
        const updateForm = discountUpdateModal.querySelector('form');
        const updateModalInstance = bootstrap.Modal.getOrCreateInstance(discountUpdateModal);
        const updateName = discountUpdateModal.querySelector('#discount-update-name');
        const updatePlan = discountUpdateModal.querySelector('#discount-update-plan');
        const updateApplicable = discountUpdateModal.querySelector('#discount-update-applicable-for');
        const updateValidFrom = discountUpdateModal.querySelector('#discount-update-valid-from');
        const updateValidTill = discountUpdateModal.querySelector('#discount-update-valid-till');
        const updateType = discountUpdateModal.querySelector('#discount-update-type');
        const updateValue = discountUpdateModal.querySelector('#discount-update-value');
        const valueSuffix = discountUpdateModal.querySelector('#discount-update-value-suffix');
        const updateStatus = discountUpdateModal.querySelector('#discount-update-status');
        const productsWrapper = discountUpdateModal.querySelector('#discount-update-products-wrapper');
        const productsSelect = discountUpdateModal.querySelector('#discount-update-products');
        const quantityWrapper = discountUpdateModal.querySelector('#discount-update-quantity-wrapper');
        const quantityInput = discountUpdateModal.querySelector('#discount-update-quantity');
        const dayInputs = Array.from(discountUpdateModal.querySelectorAll('#discount-update-days input[name="days[]"]'));

        const normalizeArray = (value) => Array.isArray(value) ? value : [];

        const toggleProducts = () => {
            if (!productsWrapper || !updateApplicable) return;
            const show = updateApplicable.value === 'specific';
            productsWrapper.classList.toggle('d-none', !show);
        };

        const toggleQuantity = () => {
            if (!quantityWrapper || !updatePlan) return;
            const show = updatePlan.value === 'decrement';
            quantityWrapper.classList.toggle('d-none', !show);
            if (!show && quantityInput) {
                quantityInput.value = '';
            }
        };

        const refreshSuffix = () => {
            if (!valueSuffix || !updateType) return;
            valueSuffix.textContent = updateType.value === 'percent' ? '%' : 'IDR';
        };

        const setProducts = (products) => {
            if (!productsSelect) return;
            const normalized = normalizeArray(products).map((value) => String(value));
            Array.from(productsSelect.options).forEach((option) => {
                option.selected = normalized.includes(option.value);
            });
        };

        const setDays = (days) => {
            const normalized = normalizeArray(days).map((value) => String(value).toLowerCase());
            dayInputs.forEach((input) => {
                input.checked = normalized.includes(input.value.toLowerCase());
            });
        };

        if (updateApplicable) {
            updateApplicable.addEventListener('change', toggleProducts);
        }
        if (updatePlan) {
            updatePlan.addEventListener('change', toggleQuantity);
        }
        if (updateType) {
            updateType.addEventListener('change', refreshSuffix);
        }

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('.edit-btn-table');
            if (!trigger || !updateForm) return;
            const payloadRaw = trigger.getAttribute('data-discount');
            if (!payloadRaw) return;

            let payload;
            try { payload = JSON.parse(payloadRaw); } catch (err) { return; }

            updateForm.reset();

            let action = trigger.getAttribute('data-update-action');
            if (!action && updateForm.dataset.actionTemplate && payload?.id) {
                action = updateForm.dataset.actionTemplate.replace('__discount__', payload.id);
            }
            if (action) updateForm.action = action;

            if (updateName) updateName.value = payload.name ?? '';
            if (updatePlan) updatePlan.value = payload.quantity_type ?? 'unlimited';
            if (updateApplicable) updateApplicable.value = payload.applicable_for ?? 'all';
            if (updateValidFrom) updateValidFrom.value = payload.valid_from ?? '';
            if (updateValidTill) updateValidTill.value = payload.valid_till ?? '';
            if (updateType) updateType.value = payload.discount_type ?? 'flat';
            if (updateValue) updateValue.value = payload.value ?? '';
            if (updateStatus) updateStatus.value = payload.status ?? 'active';
            if (quantityInput) quantityInput.value = payload.quantity ?? '';

            setProducts(payload.products ?? []);
            setDays(payload.days ?? []);
            toggleProducts();
            toggleQuantity();
            refreshSuffix();

            updateModalInstance.show();
        });
    }

    // option section (guard elements may not exist on all pages)
    const toggleOptions = document.getElementById('toggleOptions');
    const optionsSection = document.getElementById('optionsSection');
    if (toggleOptions && optionsSection) {
        toggleOptions.addEventListener('change', function () {
            optionsSection.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Single-option (edit page) add-row support remains above. Below is multi-option support for product create page.
    (function(){
        const groupsContainer = document.getElementById('optionsGroups');
        const addGroupBtn = document.getElementById('addOptionGroup');
        if (!groupsContainer) return; // not on product create

        let nextGroupIndex = 1; // first group is 0

        const renderGroup = (idx) => {
            return `
            <div class="option-group border rounded p-3" data-index="${idx}" data-next-value-index="1">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Option Name</label>
                        <input type="text" name="options[${idx}][name]" class="form-control" placeholder="e.g. Ice Level">
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mt-4">
                            <input class="form-check-input toggle-default-only" type="checkbox" name="options[${idx}][default_only]" value="1" id="opt${idx}DefaultOnly">
                            <label class="form-check-label" for="opt${idx}DefaultOnly">Default only</label>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-option-group">Remove</button>
                    </div>
                </div>
                <div class="default-wrap mt-2" style="display:none;">
                    <label class="form-label">Default value</label>
                    <input type="text" class="form-control" name="options[${idx}][default_value]" placeholder="Default" value="Default">
                    <div class="form-text">When default only is on, only this single value is used.</div>
                </div>
                <div class="values-wrap mt-2">
                    <table class="table table-bordered mb-2 option-values-table">
                        <thead>
                            <tr>
                                <th>Value</th>
                                <th>Price Change</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="options[${idx}][values][0][value]" class="form-control" placeholder="Normal Ice"></td>
                                <td><input type="number" step="0.01" name="options[${idx}][values][0][price_change]" class="form-control" placeholder="0.00"></td>
                                <td><button type="button" class="btn btn-outline-danger btn-sm removeRow">&times;</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-success add-value">+ Add Value</button>
                    </div>
                </div>
            </div>
            `;
        };

        if (addGroupBtn) {
            addGroupBtn.addEventListener('click', function(){
                groupsContainer.insertAdjacentHTML('beforeend', renderGroup(nextGroupIndex));
                nextGroupIndex++;
            });
        }

        // Toggle default-only per group
        document.addEventListener('change', function(e){
            if (!e.target.classList?.contains('toggle-default-only')) return;
            const group = e.target.closest('.option-group');
            if (!group) return;
            const defaultWrap = group.querySelector('.default-wrap');
            const valuesWrap = group.querySelector('.values-wrap');
            const addBtn = group.querySelector('.add-value');
            const checked = e.target.checked;
            if (defaultWrap && valuesWrap) {
                defaultWrap.style.display = checked ? '' : 'none';
                valuesWrap.style.display = checked ? 'none' : '';
                // Disable values inputs when default-only
                valuesWrap.querySelectorAll('input').forEach(inp => { inp.disabled = checked; });
            }
            if (addBtn) addBtn.disabled = checked;
        });

        // Add value row within a group
        document.addEventListener('click', function(e){
            if (!e.target.classList?.contains('add-value')) return;
            const group = e.target.closest('.option-group');
            if (!group) return;
            if (group.querySelector('.toggle-default-only')?.checked) return; // disabled when default-only
            const idx = group.getAttribute('data-index');
            let nextVal = parseInt(group.getAttribute('data-next-value-index') || '1', 10);
            const tbody = group.querySelector('.option-values-table tbody');
            if (!tbody) return;
            const row = `
                <tr>
                    <td><input type="text" name="options[${idx}][values][${nextVal}][value]" class="form-control" placeholder="Less Ice"></td>
                    <td><input type="number" step="0.01" name="options[${idx}][values][${nextVal}][price_change]" class="form-control" placeholder="0.00"></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm removeRow">&times;</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
            group.setAttribute('data-next-value-index', String(nextVal + 1));
        });

        // Remove option group
        document.addEventListener('click', function(e){
            if (!e.target.classList?.contains('remove-option-group')) return;
            const group = e.target.closest('.option-group');
            if (!group) return;
            group.remove();
        });
    })();

    // Fallback for edit page: add rows to single option values table
    (function(){
        const addRowBtn = document.getElementById('addRow');
        if (!addRowBtn) return;
        let rowCount = 1;
        addRowBtn.addEventListener('click', function(){
            const tableBody = document.querySelector('#optionValuesTable tbody');
            if (!tableBody) return;
            const newRow = `
                <tr>
                    <td><input type="text" name="values[${rowCount}][value]" class="form-control" placeholder="Medium" required></td>
                    <td><input type="number" step="0.01" name="values[${rowCount}][price_change]" class="form-control" placeholder="0.00"></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm removeRow">&times;</button></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);
            rowCount++;
        });
    })();

    document.addEventListener('click', function(e) {
        if (e.target.classList?.contains('removeRow')) {
            e.target.closest('tr')?.remove();
        }
    });
});

// KDS live timers: update every second without fighting Livewire
(function(){
    const fmt = (secs) => {
        secs = Math.max(0, Math.floor(secs));
        const m = Math.floor(secs / 60), s = secs % 60;
        return `${m}:${String(s).padStart(2,'0')}`;
    };
    const tick = () => {
        const now = Date.now();
        document.querySelectorAll('[data-role="kds-timer"]').forEach(el => {
            if ((el.dataset.stage || '') === 'ready') return; // do not tick done orders
            const card = el.closest('.ko-card');
            const epochSec = parseInt(el.dataset.startEpoch || '0', 10);
            // Convert seconds -> ms when epoch is provided
            let start = epochSec > 0 ? (epochSec * 1000) : Date.parse(el.dataset.start || '');
            if (!isFinite(start)) start = now;
            const warn = parseInt(el.dataset.warn || '300', 10);
            const danger = parseInt(el.dataset.danger || '600', 10);
            const elapsed = Math.floor((now - start) / 1000);
            el.textContent = fmt(elapsed);
            // colorize timer background
            el.classList.remove('kds-time-ok','kds-time-warn','kds-time-danger');
            let pr = '';
            if (elapsed >= danger) pr = 'priority-danger';
            else if (elapsed >= warn) pr = 'priority-warn';
            else pr = 'priority-ok';
            if (pr === 'priority-danger') el.classList.add('kds-time-danger');
            else if (pr === 'priority-warn') el.classList.add('kds-time-warn');
            else el.classList.add('kds-time-ok');
            if (!card) return;
            // keep reordering to surface danger items
            card.style.order = (pr === 'priority-danger') ? '-1' : (pr === 'priority-warn' ? '0' : '1');
        });
    };
    setInterval(tick, 1000);
    if (document.readyState !== 'loading') tick();
    else document.addEventListener('DOMContentLoaded', tick);
    // Nudge updates after Livewire morphs insert new nodes
    // Throttle DOM-mutation-driven updates to avoid floods
    let moQueued = false;
    const scheduleTick = () => {
        if (moQueued) return;
        moQueued = true;
        requestAnimationFrame(() => { moQueued = false; tick(); });
    };
    const mo = new MutationObserver(scheduleTick);
    const target = document.querySelector('.kitchen-board') || document.body;
    mo.observe(target, { childList: true, subtree: true });
})();


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
// Toast success
document.addEventListener('DOMContentLoaded', function () {
    const button = document.querySelector('.close-toast');
    const toast = document.getElementById('toast');
    const progress = document.querySelector('.toast-progress');

    if (!toast || !progress) return;

    // Start progress bar animation
    setTimeout(() => {
        progress.style.width = '100%';
    }, 100);

    // Auto-dismiss after 5s
    const timeout = setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
    }, 5100);

    // Manual close
    if (button) {
        button.addEventListener('click', () => {
            clearTimeout(timeout);
            toast.classList.remove('show');
            toast.classList.add('hide');
        });
    }
});
