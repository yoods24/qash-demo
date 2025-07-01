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


