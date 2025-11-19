<x-backoffice.layout>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h1 class="h3 fw-bold mb-1">Customers</h1>
                        <p class="text-muted mb-0">All guests saved for this tenant, including their order history snapshots.</p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <livewire:backoffice.tables.customers-table />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backoffice.layout>
