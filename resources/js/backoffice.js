import 'bootstrap'; 
  
  
  // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //   return new bootstrap.Tooltip(tooltipTriggerEl);
    // });

    // Sidebar toggle
document.addEventListener("DOMContentLoaded", function () {
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
            document.documentElement.dataset.theme = theme;
            try { localStorage.setItem(key, theme); } catch (e) {}
            setIcon(theme);
        };

        // Initialize icon from current theme
        const current = document.documentElement.dataset.theme || 'light';
        setIcon(current);

        if (btn) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const next = (document.documentElement.dataset.theme === 'dark') ? 'light' : 'dark';
                apply(next);
            });
        }
    })();

    const sidebar = document.querySelector('.sidebar');
    const desktopToggle = document.querySelector('#sidebarToggleDesktop');
    const mobileToggle = document.querySelector('#sidebarToggleMobile');

    // Helper: whether viewport is mobile
    const isMobile = () => window.innerWidth <= 768;

    // Desktop nav toggle: shrink on desktop, open on mobile
    if (desktopToggle) {
        desktopToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (!sidebar) return;
            if (isMobile()) {
                sidebar.classList.toggle('open');
                sidebar.classList.remove('shrunk');
                document.body.classList.toggle('sidebar-open', sidebar.classList.contains('open'));
            } else {
                sidebar.classList.toggle('shrunk');
            }
        });
    }

    // In-sidebar mobile toggle: close/open overlay
    if (mobileToggle) {
        mobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (!sidebar) return;
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open', sidebar.classList.contains('open'));
        });
    }

    // Close sidebar when resizing up to desktop
    window.addEventListener('resize', () => {
        if (!sidebar) return;
        if (!isMobile()) {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
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

    confirmModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const itemId = button.getAttribute('data-id');
        const itemTitle = button.getAttribute('data-title');
        const deleteUrl = button.getAttribute('data-url');

        const titleElement = confirmModal.querySelector('#modalItemTitle');
        const idElement = confirmModal.querySelector('#modalItemId');
        const form = confirmModal.querySelector('#deleteForm');

        titleElement.textContent = `"${itemTitle}"`;
        idElement.textContent = `"${itemId}"`
        form.action = deleteUrl;
    });
});
// option section
document.getElementById('toggleOptions').addEventListener('change', function () {
    document.getElementById('optionsSection').style.display = this.checked ? 'block' : 'none';
});

let rowCount = 1;

document.getElementById('addRow').addEventListener('click', function() {
    let tableBody = document.querySelector('#optionValuesTable tbody');
    let newRow = `
        <tr>
            <td><input type="text" name="values[${rowCount}][value]" class="form-control" placeholder="Medium" required></td>
            <td><input type="number" step="0.01" name="values[${rowCount}][price_change]" class="form-control" placeholder="0.00"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">&times;</button></td>
        </tr>
    `;
    tableBody.insertAdjacentHTML('beforeend', newRow);
    rowCount++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeRow')) {
        e.target.closest('tr').remove();
    }
});



// Toast success
document.addEventListener('DOMContentLoaded', function () {
    const button = document.querySelector('.close-toast');
    const toast = document.getElementById('toast');
    const progress = document.querySelector('.toast-progress');

    // Start progress bar animation
    setTimeout(() => {
        progress.style.width = '100%';
    }, 100); // slight delay to trigger transition

    // Auto-dismiss after 5s
    const timeout = setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
    }, 5100); // match the CSS animation

    // Manual close
    button.addEventListener('click', () => {
        clearTimeout(timeout);
        toast.classList.remove('show');
        toast.classList.add('hide');
    });
});
