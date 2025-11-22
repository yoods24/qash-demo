<x-backoffice.settings-layout>
    <div class="col-md-9">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1">Invoice Templates</h5>
                        <p class="text-muted mb-0">Select the layout that best represents your brand. Changes apply instantly.</p>
                    </div>
                    <div class="btn-group invoice-template-tabs" role="group" aria-label="Invoice types">
                        <button type="button" class="btn btn-outline-secondary active">Invoices</button>
                        <button type="button" class="btn btn-outline-secondary">Purchases</button>
                        <button type="button" class="btn btn-warning text-white">Receipts</button>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-3">
                    @foreach($templates as $key => $template)
                        @php
                            $isSelected = $selectedTemplate === $key;
                        @endphp
                        <div class="col-md-4">
                            <form method="POST" action="{{ route('backoffice.invoice-templates.select') }}" class="invoice-template-card {{ $isSelected ? 'is-selected' : '' }} h-100">
                                @csrf
                                <input type="hidden" name="template" value="{{ $key }}">
                                <div class="template-preview">
                                    <div class="invoice-header text-center">
                                        <div class="logo-circle mb-2">FM</div>
                                        <div class="fw-semibold">{{ $template['title'] }}</div>
                                        <small class="text-muted">Receipt #{{ strtoupper($key) }}</small>
                                    </div>
                                    <div class="invoice-body mt-3">
                                        <div class="d-flex justify-content-between small">
                                            <span>Customer</span>
                                            <span>John Smith</span>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span>Date</span>
                                            <span>12 Oct 2024</span>
                                        </div>
                                        <hr>
                                        <div class="small">
                                            <div class="d-flex justify-content-between"><span>Burger Combo</span><span>$18.00</span></div>
                                            <div class="d-flex justify-content-between"><span>Fresh Juice</span><span>$6.00</span></div>
                                            <div class="d-flex justify-content-between fw-semibold mt-2"><span>Total</span><span>$24.00</span></div>
                                        </div>
                                    </div>
                                    <div class="invoice-footer mt-3 text-center small text-muted">
                                        @if($key === 'template_2')
                                            Modern double column layout with accent line.
                                        @elseif($key === 'template_3')
                                            Minimal card with bordered totals.
                                        @else
                                            Classic receipt style with barcode area.
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-semibold">{{ $template['title'] }}</div>
                                            <small class="text-muted">{{ $template['description'] }}</small>
                                        </div>
                                        @if($isSelected)
                                            <span class="badge bg-success">Selected</span>
                                        @endif
                                    </div>
                                    <button type="submit" class="btn {{ $isSelected ? 'btn-success' : 'btn-outline-primary' }} w-100 mt-3">
                                        {{ $isSelected ? 'Currently in Use' : 'Use this Template' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-backoffice.settings-layout>
