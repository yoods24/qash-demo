<x-backoffice.layout>
    <!-- Mobile orientation guard: visible only on phones in portrait via JS -->
    <div id="rotateOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 p-4" style="z-index:2000; background: rgba(255,255,255,0.98);">
        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center">
            <i class="bi bi-phone-landscape" style="font-size: 3rem;"></i>
            <h5 class="mt-2 mb-1">Rotate to Landscape</h5>
            <div class="text-muted">For the table plan, please rotate your device horizontally.</div>
        </div>
    </div>
@livewire('backoffice.tables.plan-board')
    <script>
        (function(){
            const overlay = document.getElementById('rotateOverlay');
            if (!overlay) return;
            function small() { return window.matchMedia('(max-width: 991.98px)').matches; }
            function portrait() { const m = window.matchMedia('(orientation: portrait)'); return (m && typeof m.matches==='boolean') ? m.matches : (innerHeight > innerWidth); }
            function toggle(){ overlay.classList.toggle('d-none', !(small() && portrait())); }
            toggle();
            window.addEventListener('resize', toggle);
            window.addEventListener('orientationchange', toggle);
        })();
</script>

    
</x-backoffice.layout>
