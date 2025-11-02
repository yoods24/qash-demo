<x-backoffice.layout>
    <div class="mb-3">
        <h4 class="mb-1">Table Information</h4>
        <div class="text-muted small">
            Overview of all dining tables and floors for tenant {{ tenant('id') }}. Use the Floor filter to view specific floors or all tables.
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @livewire('backoffice.tables.table-info')
        </div>
    </div>

    <div class="mt-3 small text-muted">
        Tip: Generate a QR for a table by constructing a URL to the customer order page with the table id, for example:
        <code>/t/{{ tenant('id') }}/order?table=&lt;TABLE_ID&gt;</code>. You can render this as a QR code using any QR generator in print materials.
    </div>
</x-backoffice.layout>

