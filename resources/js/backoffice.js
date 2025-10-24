import 'bootstrap';
  
  
  // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //   return new bootstrap.Tooltip(tooltipTriggerEl);
    // });



    // Sidebar toggle
document.addEventListener("DOMContentLoaded", function () {
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

    // option section (guard elements may not exist on all pages)
    const toggleOptions = document.getElementById('toggleOptions');
    const optionsSection = document.getElementById('optionsSection');
    if (toggleOptions && optionsSection) {
        toggleOptions.addEventListener('change', function () {
            optionsSection.style.display = this.checked ? 'block' : 'none';
        });
    }

    let rowCount = 1;
    const addRowBtn = document.getElementById('addRow');
    if (addRowBtn) {
        addRowBtn.addEventListener('click', function() {
            const tableBody = document.querySelector('#optionValuesTable tbody');
            if (!tableBody) return;
            const newRow = `
                <tr>
                    <td><input type="text" name="values[${rowCount}][value]" class="form-control" placeholder="Medium" required></td>
                    <td><input type="number" step="0.01" name="values[${rowCount}][price_change]" class="form-control" placeholder="0.00"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', newRow);
            rowCount++;
        });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList?.contains('removeRow')) {
            e.target.closest('tr')?.remove();
        }
    });
});



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
