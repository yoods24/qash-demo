 import 'bootstrap'; 
  
  
  // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //   return new bootstrap.Tooltip(tooltipTriggerEl);
    // });

    // Sidebar toggle
document.addEventListener("DOMContentLoaded", function () {
    const toggles = document.querySelectorAll('#sidebarToggleDesktop, #sidebarToggleMobile');

    toggles.forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('shrunk');
        });
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