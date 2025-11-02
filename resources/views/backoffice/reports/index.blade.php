<x-backoffice.layout>
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-bar-chart-line text-orange fs-3 me-2"></i>
        <h4 class="mb-0">Reports</h4>
    </div>

    <h6 class="text-muted fw-bold mb-3">Restaurant Sales Reports</h6>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <a href="{{ route('backoffice.reports.sales', ['tenant' => tenant('id')]) }}" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-cash"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Sales Report</div>
                        <div class="text-muted small">A detailed breakdown of sales performance within a selected time range.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="{{ route('backoffice.reports.products', ['tenant' => tenant('id')]) }}" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-box-seam"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Products Purchase Report</div>
                        <div class="text-muted small">Analyze product procurement performance across different periods.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-person-lines-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Sales By Creator Report</div>
                        <div class="text-muted small">Shows total sales grouped by the user who created each order.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-person-badge"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Sales By Cashier Report</div>
                        <div class="text-muted small">Summarizes total sales handled by each cashier, excluding canceled and refunded orders.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-receipt"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Tax Report</div>
                        <div class="text-muted small">Summarizes collected taxes by order status, type, and payment over a selected period.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-receipt-cutoff"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Product Tax Report</div>
                        <div class="text-muted small">Displays the total tax amounts applied to specific products within a selected period.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-shop"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Branch Performance Report</div>
                        <div class="text-muted small">Shows key sales and order metrics for each branch to compare performance over time.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-wallet2"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Payments Report</div>
                        <div class="text-muted small">Breaks down received payments by method to track revenue collection.</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <h6 class="text-muted fw-bold mb-3">POS Reports</h6>
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-pc-display"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Register Summary Report</div>
                        <div class="text-muted small">Financial summary of POS registers including sessions, sales, cash movements, and balances.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-cash-coin"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Cash Movement Report</div>
                        <div class="text-muted small">Tracks all cash-in and cash-out transactions across POS registers, including users and reasons.</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <h6 class="text-muted fw-bold mb-3">Inventory Reports</h6>
    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-clipboard-data"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Stock On Hand</div>
                        <div class="text-muted small">Current inventory levels by product, category, and variant.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-arrow-left-right"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Product Movement</div>
                        <div class="text-muted small">Inbound, outbound, and adjustments by date range.</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="#" class="report-card d-block p-3 rounded-4 bg-white text-decoration-none shadow-sm h-100">
                <div class="d-flex align-items-start">
                    <div class="report-icon me-3"><i class="bi bi-bar-chart"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Inventory Valuation</div>
                        <div class="text-muted small">Valuation of stock by cost method to support accounting.</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-backoffice.layout>
