<x-backoffice.layout>
    <div class="mb-3">
        <h5 class="mb-1">QR Code for {{ $table->label }}</h5>
        <div class="text-muted small">Tenant: {{ $tenantId }}</div>
        @if (session('status'))
            <div class="alert alert-success py-1 my-2">{{ session('status') }}</div>
        @endif
        <div class="small">Scan URL: <a href="{{ $scanUrl }}" target="_blank">{{ $scanUrl }}</a></div>
        <div class="small mt-1">Current code: <code>{{ $table->qr_code ?? '— none —' }}</code></div>
    </div>

    <div class="card">
        <div class="card-body d-flex justify-content-center">
            <div id="qrcode"></div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2 align-items-center">
        <form action="{{ route('backoffice.tables.qr.generate', ['tenant' => $tenantId, 'dining_table' => $table->id]) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">Generate a new code</button>
        </form>
        <button id="printBtn" class="btn btn-outline-secondary">Print</button>
        <a href="{{ route('backoffice.tables.info', ['tenant' => $tenantId]) }}" class="btn btn-secondary">Back</a>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const url = @json($scanUrl);
            const el = document.getElementById('qrcode');
            // Generate QR
            new QRCode(el, { text: url, width: 256, height: 256, correctLevel: QRCode.CorrectLevel.M });
            // Print helper
            document.getElementById('printBtn').addEventListener('click', () => window.print());
        });
    </script>
</x-backoffice.layout>
