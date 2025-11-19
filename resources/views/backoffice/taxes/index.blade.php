<x-backoffice.layout>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="fw-bold">Taxes</h2>
                        <p class="text-muted mb-0">Create PPN, service fees, and other tenant-specific taxes.</p>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-outline-secondary">Export</button>
                        <button class="btn btn-outline-secondary">More actions</button>
                        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            Add Tax
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body">
                        <livewire:backoffice.tables.taxes-table />
                    </div>
                </div>
            </div>
        </div>
    </div>

<x-modal.create id="addProductModal" title="Add Tax" action="{{ route('backoffice.taxes.store') }}">
    
    {{-- Tax Name --}}
    <div class="mb-3">
        <label for="tax_name" class="form-label">Name <span class="text-danger">*</span></label>
        <input 
            type="text" 
            class="form-control" 
            id="tax_name" 
            name="name" 
            placeholder="VAT, Service Fee, etc." 
            required
        >
    </div>

    {{-- Tax Type --}}
    <div class="mb-3">
        <label for="tax_type" class="form-label">Type <span class="text-danger">*</span></label>
        <select 
            class="form-select" 
            id="tax_type" 
            name="type" 
            required
        >
            <option value="" selected disabled>Select an option</option>
            <option value="percentage">Percentage</option>
            <option value="fixed">Fixed Amount</option>
        </select>
    </div>

    {{-- Rate --}}
    <div class="mb-3">
        <label for="tax_rate" class="form-label">Rate <span class="text-danger">*</span></label>

        <div class="input-group">
            <input 
                type="number" 
                step="0.01" 
                min="0"
                class="form-control"
                id="tax_rate"
                name="rate"
                placeholder="10 for 10%, or 1000 for fixed amount"
                required
            >
            <span class="input-group-text" id="rateSuffix">%</span>
        </div>

        {{-- JS auto-change % to Rp depending on selection --}}
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const typeSelect = document.getElementById("tax_type");
                const suffix = document.getElementById("rateSuffix");

                typeSelect.addEventListener("change", function () {
                    suffix.innerText = this.value === "percentage" ? "%" : "Rp";
                });
            });
        </script>
    </div>

    {{-- Active Toggle --}}
    <div class="form-check form-switch mb-3">
        <input type="hidden" name="is_active" value="0">
        <input 
            class="form-check-input" 
            type="checkbox" 
            role="switch" 
            id="tax_active" 
            name="is_active"
            value="1"
            checked
        >
        <label class="form-check-label" for="tax_active">Active</label>
    </div>

</x-modal.create>

</x-backoffice.layout>
