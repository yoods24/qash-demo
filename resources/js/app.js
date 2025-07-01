import 'bootstrap';

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