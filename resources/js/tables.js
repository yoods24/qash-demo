import 'gridstack/dist/gridstack.min.css';
import { GridStack } from 'gridstack';

function getHeaders(csrf) {
  return {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrf,
  };
}

function collectPositions(container) {
  const items = [];
  container.querySelectorAll('.grid-stack-item').forEach((el) => {
    const id = parseInt(el.dataset.id);
    const x = parseInt(el.getAttribute('gs-x') || '0');
    const y = parseInt(el.getAttribute('gs-y') || '0');
    const w = parseInt(el.getAttribute('gs-w') || '1');
    const h = parseInt(el.getAttribute('gs-h') || '1');
    if (!isNaN(id)) items.push({ id, x, y, w, h });
  });
  return items;
}

document.addEventListener('DOMContentLoaded', () => {
  const gridEl = document.getElementById('tablesGrid');
  if (!gridEl) return;

  const csrf = gridEl.dataset.csrf;
  const storeUrl = gridEl.dataset.storeUrl;
  const positionsUrl = gridEl.dataset.positionsUrl;
  const updateUrlTemplate = gridEl.dataset.updateUrlTemplate; // e.g. /tables/
  const destroyUrlTemplate = gridEl.dataset.destroyUrlTemplate; // e.g. /tables/
  const floorId = parseInt(gridEl.dataset.floorId || '0');

  const grid = GridStack.init({
    cellHeight: 80,
    minRow: 6,
    column: 12,
    oneColumnModeMaxWidth: 0,
    float: true,
    animate: true,
    resizable: { handles: 'e, se, s, sw, w' },
    draggable: { handle: '.grid-stack-item-content' },
  }, gridEl);

  // Inject minimal CSS for selection highlight
  const style = document.createElement('style');
  style.textContent = `
    .grid-stack-item .grid-stack-item-content.qash-selected { outline: 3px solid #f59e0b; outline-offset: -3px; }
  `;
  document.head.appendChild(style);

  let selectedEl = null;

  function statusBorderColor(status) {
    switch ((status || '').toLowerCase()) {
      case 'available': return '#16a34a';
      case 'occupied': return '#dc2626';
      case 'oncleaning': return '#f59e0b';
      case 'archived': return '#111827';
      default: return '#e2e8f0';
    }
  }

  function applyTileStyles(tileEl) {
    const content = tileEl.querySelector('.grid-stack-item-content');
    if (!content) return;
    const shape = tileEl.dataset.shape || 'rectangle';
    const status = tileEl.dataset.status || 'available';
    content.style.border = '2px solid ' + statusBorderColor(status);
    if (shape === 'circle') {
      content.style.borderRadius = '50%';
      content.style.aspectRatio = '1 / 1';
    } else {
      content.style.borderRadius = '0.375rem';
      content.style.aspectRatio = '';
    }
  }

  function normalizeTileText(tileEl) {
    const content = tileEl.querySelector('.grid-stack-item-content');
    if (!content) return;
    const meta = content.querySelector('.small.text-muted');
    if (!meta) return;
    const status = (tileEl.dataset.status || 'available');
    const capacity = parseInt(tileEl.dataset.capacity || '0');
    const s = status.charAt(0).toUpperCase() + status.slice(1);
    meta.textContent = `${s} - ${isNaN(capacity) ? 0 : capacity} seats`;
  }

  function bindTileClicks() {
    gridEl.querySelectorAll('.grid-stack-item').forEach((el) => {
      el.addEventListener('click', (e) => {
        // Avoid double-handling when clicking edit button
        if (e.target.closest('.edit-table-btn')) return;
        selectTile(el);
      });
    });
    gridEl.querySelectorAll('.edit-table-btn').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const el = e.currentTarget.closest('.grid-stack-item');
        if (el) selectTile(el);
      });
    });
  }

  function selectTile(el) {
    // Clear previous selection highlight
    if (selectedEl) {
      selectedEl.querySelector('.grid-stack-item-content')?.classList.remove('qash-selected');
    }
    selectedEl = el;

    // Visual selection
    selectedEl.querySelector('.grid-stack-item-content')?.classList.add('qash-selected');

    // Populate form
    const id = el.dataset.id;
    const form = document.getElementById('editTableForm');
    if (!form) return;
    form.querySelector('#tableId').value = id;
    form.querySelector('#tableLabel').value = el.dataset.label || '';
    form.querySelector('#tableStatus').value = el.dataset.status || 'available';
    form.querySelector('#tableShape').value = el.dataset.shape || 'rectangle';
    form.querySelector('#tableCapacity').value = el.dataset.capacity || 2;
    const color = el.dataset.color || '#f1f5f9';
    form.querySelector('#tableColor').value = /^#/.test(color) ? color : '#f1f5f9';

    // Enable Save/Delete when selected
    const saveBtn = form.querySelector('button[type="submit"]');
    const delBtn = document.getElementById('deleteTableBtn');
    if (saveBtn) saveBtn.disabled = false;
    if (delBtn) delBtn.disabled = false;
  }

  bindTileClicks();

  // Auto-select first tile if present
  const first = gridEl.querySelector('.grid-stack-item');
  if (first) selectTile(first);

  // Apply initial shape/status styling and normalize meta text
  gridEl.querySelectorAll('.grid-stack-item').forEach((el) => {
    applyTileStyles(el);
    normalizeTileText(el);
  });

  // Add table
  const addBtn = document.getElementById('addTableBtn');
  if (addBtn && storeUrl) {
    addBtn.addEventListener('click', async () => {
      addBtn.disabled = true;
      try {
        const res = await fetch(storeUrl, {
          method: 'POST',
          headers: { ...getHeaders(csrf), 'Accept': 'application/json' },
          body: JSON.stringify({ floor_id: floorId }),
        });
        const data = res.ok ? await res.json() : null;
        if (data?.ok && data.table) {
          const t = data.table;
          const content = document.createElement('div');
          content.className = 'grid-stack-item-content d-flex flex-column justify-content-center align-items-center';
          content.style.background = t.color || '#f1f5f9';
          content.innerHTML = `
            <div class="fw-semibold">${t.label}</div>
            <div class="small text-muted">${(t.status || 'available').charAt(0).toUpperCase() + (t.status || 'available').slice(1)} • ${t.capacity} seats</div>
            <button class="btn btn-sm btn-outline-secondary mt-2 edit-table-btn" data-id="${t.id}">
                <i class="bi bi-pencil-square"></i>
            </button>
          `;

          const widget = document.createElement('div');
          widget.className = 'grid-stack-item';
          widget.dataset.id = t.id;
          widget.dataset.label = t.label;
          widget.dataset.status = t.status;
          widget.dataset.shape = t.shape;
          widget.dataset.capacity = t.capacity;
          widget.dataset.color = t.color || '';

          grid.addWidget(widget, {x: t.x ?? 0, y: t.y ?? 0, w: t.w ?? 2, h: t.h ?? 2, minW: 1, minH: 1});
          widget.appendChild(content);
          applyTileStyles(widget);
          // Normalize meta text to avoid encoding glitches
          const meta = content.querySelector('.small.text-muted');
          if (meta) {
            const statusText = (t.status || 'available');
            meta.textContent = `${statusText.charAt(0).toUpperCase() + statusText.slice(1)} - ${t.capacity} seats`;
          }
          normalizeTileText(widget);
          bindTileClicks();
          selectTile(widget);
        }
      } finally {
        addBtn.disabled = false;
      }
    });
  }

  // Save positions
  const saveBtn = document.getElementById('saveLayoutBtn');
  if (saveBtn && positionsUrl) {
    saveBtn.addEventListener('click', async () => {
      saveBtn.disabled = true;
      try {
        const payload = { positions: collectPositions(gridEl), floor_id: floorId };
        await fetch(positionsUrl, {
          method: 'PUT',
          headers: { ...getHeaders(csrf), 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        saveBtn.classList.add('btn-success');
        setTimeout(() => saveBtn.classList.remove('btn-success'), 800);
      } finally {
        saveBtn.disabled = false;
      }
    });
  }

  // Edit form handling
  const form = document.getElementById('editTableForm');
  const deleteBtn = document.getElementById('deleteTableBtn');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = form.querySelector('#tableId').value;
      if (!id) return;
      const url = updateUrlTemplate + id;
      const body = {
        label: form.querySelector('#tableLabel').value,
        status: form.querySelector('#tableStatus').value,
        shape: form.querySelector('#tableShape').value,
        capacity: parseInt(form.querySelector('#tableCapacity').value || '2'),
        color: form.querySelector('#tableColor').value,
      };
      const res = await fetch(url, { method: 'PUT', headers: { ...getHeaders(csrf), 'Accept': 'application/json' }, body: JSON.stringify(body) });
      const data = res.ok ? await res.json() : null;
      if (data?.ok && data.table) {
        const t = data.table;
        const el = gridEl.querySelector(`.grid-stack-item[data-id="${t.id}"]`);
        if (el) {
          el.dataset.label = t.label;
          el.dataset.status = t.status;
          el.dataset.shape = t.shape;
          el.dataset.capacity = t.capacity;
          el.dataset.color = t.color || '';
          const content = el.querySelector('.grid-stack-item-content');
          if (content) {
            content.style.background = t.color || '#f1f5f9';
            content.querySelector('.fw-semibold').textContent = t.label;
            const meta = content.querySelector('.small.text-muted');
            const s = t.status.charAt(0).toUpperCase() + t.status.slice(1);
            meta.textContent = `${s} - ${t.capacity} seats`;
            applyTileStyles(el);
            meta.textContent = `${t.status.charAt(0).toUpperCase() + t.status.slice(1)} • ${t.capacity} seats`;
          }
        }
      }
    });
  }

  if (deleteBtn) {
    deleteBtn.addEventListener('click', async () => {
      const id = document.getElementById('tableId').value;
      if (!id) return;
      const url = destroyUrlTemplate + id;
      const res = await fetch(url, { method: 'DELETE', headers: { ...getHeaders(csrf), 'Accept': 'application/json' } });
      const data = res.ok ? await res.json() : null;
      if (data?.ok) {
        const el = gridEl.querySelector(`.grid-stack-item[data-id="${id}"]`);
        if (el) grid.removeWidget(el);
        form.reset();
        // Clear selection highlight and disable buttons
        if (selectedEl) {
          selectedEl.querySelector('.grid-stack-item-content')?.classList.remove('qash-selected');
        }
        selectedEl = null;
        const saveBtn = form.querySelector('button[type="submit"]');
        if (saveBtn) saveBtn.disabled = true;
        const delBtn = document.getElementById('deleteTableBtn');
        if (delBtn) delBtn.disabled = true;
      }
    });
  }
});
