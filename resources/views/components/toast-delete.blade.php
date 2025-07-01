@if(session('message'))
<div id="toast" class="toast-success-container show d-flex flex-column justify-content-between">
    <div class="d-flex justify-content-between align-items-center">
        <span class="toast-success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </span>
        <p class="m-0 ms-2 flex-grow-1 text-black"><strong>{{ session('message') }}</strong></p>
        <button class="close-toast btn text-muted">X</button>
    </div>
    <div class="toast-progress"></div>
</div>
@endif
