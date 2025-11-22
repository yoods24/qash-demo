<x-backoffice.settings-layout>
    @php
        $settings = $invoiceSettings ?? new \App\Models\TenantInvoiceSettings();
        $logoUrl = $settings->invoice_logo;
        $dueOptions = range(0, 60);
    @endphp

    <div class="col-md-9">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h5 class="mb-1">Invoice Settings</h5>
                        <p class="text-muted mb-0">Customize how your invoices look before sending them to customers.</p>
                    </div>
                    <div class="invoice-settings-preview rounded-circle border bg-light d-flex align-items-center justify-content-center">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Invoice Logo" class="img-fluid">
                        @else
                            <span class="text-muted small">Logo</span>
                        @endif
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-2">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.invoice-settings.update') }}" enctype="multipart/form-data" class="invoice-settings-form">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Invoice Logo</label>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="invoice-logo-box border rounded d-flex align-items-center justify-content-center bg-light">
                                    @if($logoUrl)
                                        <img src="{{ $logoUrl }}" alt="Invoice Logo" class="img-fluid">
                                    @else
                                        <span class="text-muted small">Preview</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="invoice_logo" accept="image/*" class="form-control @error('invoice_logo') is-invalid @enderror">
                                    <small class="text-muted">Recommended size 450x450px. Max size 5MB.</small>
                                    @error('invoice_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="invoice_prefix">Invoice Prefix</label>
                            <input type="text" id="invoice_prefix" name="invoice_prefix" class="form-control" placeholder="INV-" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}">
                            <small class="text-muted">Shown before the invoice number.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" for="invoice_due_days">Invoice Due</label>
                            <div class="input-group">
                                <select name="invoice_due_days" id="invoice_due_days" class="form-select">
                                    @foreach($dueOptions as $day)
                                        <option value="{{ $day }}" @selected(old('invoice_due_days', $settings->invoice_due_days) == $day)>{{ $day }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text">Days</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold d-block">Invoice Round Off</label>
                            <div class="d-flex align-items-center gap-3">
                                <input type="hidden" name="invoice_round_off" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="invoice_round_off"
                                           name="invoice_round_off" value="1" @checked(old('invoice_round_off', $settings->invoice_round_off))>
                                    <label class="form-check-label" for="invoice_round_off">Enable</label>
                                </div>
                                <select name="invoice_round_direction" class="form-select w-auto" aria-label="Round direction">
                                    <option value="up" @selected(old('invoice_round_direction', $settings->invoice_round_direction) === 'up')>Round Up</option>
                                    <option value="down" @selected(old('invoice_round_direction', $settings->invoice_round_direction) === 'down')>Round Down</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold d-block">Show Company Details</label>
                            <input type="hidden" name="show_company_details" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="show_company_details"
                                       name="show_company_details" value="1" @checked(old('show_company_details', $settings->show_company_details))>
                                <label class="form-check-label" for="show_company_details">Display tenant info on invoice</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="invoice_header_terms">Invoice Header Terms</label>
                            <textarea id="invoice_header_terms" name="invoice_header_terms" rows="3" class="form-control" placeholder="Add a short message or payment instruction.">{{ old('invoice_header_terms', $settings->invoice_header_terms) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="invoice_footer_terms">Invoice Footer Terms</label>
                            <textarea id="invoice_footer_terms" name="invoice_footer_terms" rows="4" class="form-control" placeholder="Include thanks note, refund policy, etc.">{{ old('invoice_footer_terms', $settings->invoice_footer_terms) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-light border">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backoffice.settings-layout>
