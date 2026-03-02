@extends('layouts.app')
@section('title', 'Reports & Analytics')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<style>
.report-header {
    background: linear-gradient(135deg, #7367f0 0%, #9e95f5 50%, #ce9ffc 100%);
    border-radius: 0.75rem;
    padding: 1.75rem 2rem;
    margin-bottom: 1.25rem;
    color: #fff;
    position: relative;
    overflow: visible;
}
.report-header::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    pointer-events: none;
}
.report-header h4 { margin: 0 0 0.25rem; font-weight: 700; font-size: 1.375rem; color: #fff; }
.report-header p  { margin: 0; opacity: 0.85; font-size: 0.875rem; }
.report-header .export-dropdown { position: relative; z-index: 2; }
.report-header .btn-export {
    background: rgba(255,255,255,0.2);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 0.5rem;
    padding: 0.45rem 1rem;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.2s;
}
.report-header .btn-export:hover,
.report-header .btn-export:focus {
    background: rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.5);
    color: #fff;
}
.report-header .btn-export::after {
    margin-left: 0.5rem;
}

.filter-bar {
    background: #fff;
    border: 1px solid #f0f0f3;
    border-radius: 0.75rem;
    padding: 0.875rem 1.25rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
.filter-bar .preset-btn {
    padding: 0.35rem 0.875rem;
    border-radius: 50rem;
    font-size: 0.8125rem;
    font-weight: 500;
    border: 1px solid #e4e6ef;
    background: #fff;
    color: #566a7f;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
}
.filter-bar .preset-btn:hover,
.filter-bar .preset-btn.active {
    background: #7367f0;
    border-color: #7367f0;
    color: #fff;
}
.filter-bar .date-range-input {
    min-width: 280px;
    font-size: 0.8125rem;
    border-radius: 50rem;
    padding: 0.35rem 0.875rem;
    border: 1px solid #e4e6ef;
    font-weight: 500;
    color: #566a7f;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.filter-bar .date-range-input:focus {
    border-color: #7367f0;
    box-shadow: 0 0 0 3px rgba(115,103,240,0.15);
    outline: none;
}
.filter-bar .period-select {
    max-width: 140px;
    font-size: 0.8125rem;
    border-radius: 50rem;
    padding: 0.35rem 2rem 0.35rem 0.875rem;
    border: 1px solid #e4e6ef;
    background-color: #fff;
    color: #566a7f;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.15s, box-shadow 0.15s;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23566a7f' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
}
.filter-bar .period-select:focus {
    border-color: #7367f0;
    box-shadow: 0 0 0 3px rgba(115,103,240,0.15);
    outline: none;
}
.filter-bar .period-select:hover {
    border-color: #7367f0;
}

.report-tabs .nav-pills .nav-link {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #566a7f;
    display: flex;
    align-items: center;
    gap: 0.375rem;
    white-space: nowrap;
}
.report-tabs .nav-pills .nav-link.active {
    background: rgba(115, 103, 240, 0.12);
    color: #7367f0;
    font-weight: 600;
}
.report-tabs .nav-pills .nav-link i { font-size: 1.125rem; }

.kpi-card {
    border: 0;
    border-radius: 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, transform 0.2s;
    overflow: hidden;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.kpi-card .kpi-icon {
    width: 42px; height: 42px;
    border-radius: 0.625rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.kpi-card .kpi-value { font-size: 1.375rem; font-weight: 700; margin: 0; }
.kpi-card .kpi-label { font-size: 0.8125rem; color: #a1acb8; margin: 0; }
.kpi-card .kpi-change {
    font-size: 0.75rem; font-weight: 600;
    padding: 0.125rem 0.5rem;
    border-radius: 50rem;
}
.kpi-card .kpi-change.up   { background: rgba(40,199,111,0.12); color: #28c76f; }
.kpi-card .kpi-change.down { background: rgba(234,84,85,0.12);  color: #ea5455; }
.kpi-card .kpi-change.flat { background: rgba(168,170,174,0.12); color: #a8aaae; }

.chart-card {
    border: 0;
    border-radius: 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 1.25rem;
}
.chart-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f0f0f3;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.chart-card .card-header h6 { margin: 0; font-size: 0.9375rem; font-weight: 600; }
.chart-card .card-body { padding: 1.25rem; }

.table-card { border: 0; border-radius: 0.75rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
.table-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f0f0f3;
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.table-card .card-header h6 { margin: 0; font-size: 0.9375rem; font-weight: 600; }
.report-table { font-size: 0.8125rem; }
.report-table thead th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #a1acb8;
    border-bottom: 2px solid #f0f0f3;
}

.skeleton-box {
    display: inline-block;
    height: 1em;
    position: relative;
    overflow: hidden;
    background-color: #e8e8e8;
    border-radius: 4px;
}
.skeleton-box::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    transform: translateX(-100%);
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}
@@keyframes shimmer { 100% { transform: translateX(100%); } }

.export-dropdown .dropdown-menu {
    min-width: 200px;
    border: 1px solid #e9ecef;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    padding: 0.375rem 0;
}
.export-dropdown .dropdown-item {
    font-size: 0.8125rem;
    padding: 0.5rem 1rem;
    color: #566a7f;
    transition: all 0.15s;
}
.export-dropdown .dropdown-item:hover {
    background: rgba(115,103,240,0.08);
    color: #7367f0;
}
.export-dropdown .dropdown-item i { width: 1.25rem; margin-right: 0.375rem; }

#reportTabContent.tab-content {
    padding: 0 !important;
    position: relative;
    min-height: 200px;
}
#reportLoadingOverlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 300px;
    background: rgba(255,255,255,0.75);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
    backdrop-filter: blur(2px);
    border-radius: 0.75rem;
}
#reportLoadingOverlay.active {
    opacity: 1;
    pointer-events: auto;
}
#reportLoadingOverlay .spinner-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    background: #fff;
    padding: 1.75rem 2.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
#reportLoadingOverlay .spinner-border {
    width: 2.5rem;
    height: 2.5rem;
    border-width: 3px;
    color: #7367f0;
}
#reportLoadingOverlay .loading-text {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #566a7f;
}
.flatpickr-calendar { z-index: 99999 !important; }

.tab-scroll-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.tab-scroll-wrapper::-webkit-scrollbar { display: none; }
</style>
@endpush

@section('content')
{{-- Header --}}
<div class="report-header d-flex align-items-center justify-content-between">
    <div>
        <h4><i class="ti tabler-chart-bar me-2"></i>Reports & Analytics</h4>
        <p>Comprehensive business insights across all operations</p>
    </div>
    <div class="dropdown export-dropdown">
        <button class="btn btn-export dropdown-toggle" type="button" id="exportDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti tabler-download me-1"></i> Export Reports
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdownBtn">
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exportModal"><i class="ti tabler-file-export"></i> Export Center</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item export-current" href="#" data-format="csv"><i class="ti tabler-file-spreadsheet"></i> Current Tab CSV</a></li>
            <li><a class="dropdown-item export-current" href="#" data-format="pdf"><i class="ti tabler-file-type-pdf"></i> Current Tab PDF</a></li>
        </ul>
    </div>
</div>

{{-- Date Range Filter --}}
<div class="filter-bar" id="filterBar">
    <button class="preset-btn active" data-range="30d">Last 30 Days</button>
    <button class="preset-btn" data-range="today">Today</button>
    <button class="preset-btn" data-range="7d">Last 7 Days</button>
    <button class="preset-btn" data-range="month">This Month</button>
    <button class="preset-btn" data-range="last-month">Last Month</button>
    <button class="preset-btn" data-range="quarter">This Quarter</button>
    <button class="preset-btn" data-range="year">This Year</button>
    <div class="ms-auto d-flex align-items-center gap-2">
        <input type="text" class="form-control date-range-input" id="dateRangeInput" placeholder="Custom range...">
        <select class="form-select period-select" id="periodSelect">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>
</div>

{{-- Tabs --}}
<div class="report-tabs mb-3">
    <div class="tab-scroll-wrapper">
        <ul class="nav nav-pills flex-nowrap" id="reportTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#tab-sales" data-tab="sales"><i class="ti tabler-shopping-cart"></i> Sales</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-revenue" data-tab="revenue"><i class="ti tabler-report-money"></i> Revenue</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-products" data-tab="products"><i class="ti tabler-package"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-customers" data-tab="customers"><i class="ti tabler-users"></i> Customers</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-sellers" data-tab="sellers"><i class="ti tabler-building-store"></i> Sellers</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-payments" data-tab="payments"><i class="ti tabler-wallet"></i> Payments</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-refunds" data-tab="refunds"><i class="ti tabler-receipt-refund"></i> Refunds</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tab-support" data-tab="support"><i class="ti tabler-ticket"></i> Support</a></li>
        </ul>
    </div>
</div>

{{-- Tab Content --}}
<div class="tab-content" id="reportTabContent">

    @foreach(['sales','revenue','products','customers','sellers','payments','refunds','support'] as $tab)
    <div class="tab-pane fade {{ $tab === 'sales' ? 'show active' : '' }}" id="tab-{{ $tab }}">

        {{-- KPI Row --}}
        <div class="row g-4 mb-4" id="{{ $tab }}-kpi-row">
            @for($i = 0; $i < 4; $i++)
            <div class="col-xl-3 col-sm-6">
                <div class="card kpi-card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <p class="kpi-label placeholder-glow"><span class="placeholder col-10"></span></p>
                                <h4 class="kpi-value placeholder-glow"><span class="placeholder col-8"></span></h4>
                            </div>
                            <div class="kpi-icon bg-label-secondary">
                                <i class="ti tabler-loader"></i>
                            </div>
                        </div>
                        <span class="kpi-change flat placeholder-glow"><span class="placeholder col-6"></span></span>
                    </div>
                </div>
            </div>
            @endfor
        </div>

        {{-- Charts Row --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 id="{{ $tab }}-chart1-title">—</h6>
                    </div>
                    <div class="card-body">
                        <div id="{{ $tab }}-chart1" style="min-height:350px"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 id="{{ $tab }}-chart2-title">—</h6>
                    </div>
                    <div class="card-body">
                        <div id="{{ $tab }}-chart2" style="min-height:350px"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card chart-card">
                    <div class="card-header">
                        <h6 id="{{ $tab }}-chart3-title">—</h6>
                    </div>
                    <div class="card-body">
                        <div id="{{ $tab }}-chart3" style="min-height:300px"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card table-card">
                    <div class="card-header">
                        <h6 id="{{ $tab }}-table-title">—</h6>
                        <div class="dropdown export-dropdown">
                            <button class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item export-tab" href="#" data-tab="{{ $tab }}" data-format="csv"><i class="ti tabler-file-spreadsheet"></i> CSV</a></li>
                                <li><a class="dropdown-item export-tab" href="#" data-tab="{{ $tab }}" data-format="pdf"><i class="ti tabler-file-type-pdf"></i> PDF</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table report-table" id="{{ $tab }}-table">
                                <thead><tr></tr></thead>
                                <tbody>
                                    <tr><td class="text-center text-muted py-4" colspan="10"><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endforeach

</div>

{{-- Export Center Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti tabler-file-export me-2"></i>Export Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Report Type</label>
                    <select class="form-select" id="exportReportType">
                        <option value="sales">Sales Overview</option>
                        <option value="revenue">Revenue & Commission</option>
                        <option value="products">Product Analytics</option>
                        <option value="customers">Customer Insights</option>
                        <option value="sellers">Seller Performance</option>
                        <option value="payments">Payment & Wallet</option>
                        <option value="refunds">Refunds & Disputes</option>
                        <option value="support">Support Tickets</option>
                        <option value="full">Full Report (All Sections)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Date Range</label>
                    <input type="text" class="form-control" id="exportDateRange" placeholder="Select date range...">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Format</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" id="expFmtCsv" value="csv" checked>
                            <label class="form-check-label" for="expFmtCsv">CSV</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exportFormat" id="expFmtPdf" value="pdf">
                            <label class="form-check-label" for="expFmtPdf">PDF</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="exportGenerateBtn">
                    <i class="ti tabler-download me-1"></i> Generate & Download
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Route URLs for JS --}}
<script>
window._currencySymbol = '{{ app(\App\Services\CurrencyService::class)->symbol() }}';
window._reportUrls = {
    sales:    "{{ route('reports.sales') }}",
    revenue:  "{{ route('reports.revenue') }}",
    products: "{{ route('reports.products') }}",
    customers:"{{ route('reports.customers') }}",
    sellers:  "{{ route('reports.sellers') }}",
    payments: "{{ route('reports.payments') }}",
    refunds:  "{{ route('reports.refunds') }}",
    support:  "{{ route('reports.support') }}",
    export: {
        sales:    "{{ route('reports.export.sales') }}",
        revenue:  "{{ route('reports.export.revenue') }}",
        products: "{{ route('reports.export.products') }}",
        customers:"{{ route('reports.export.customers') }}",
        sellers:  "{{ route('reports.export.sellers') }}",
        payments: "{{ route('reports.export.payments') }}",
        refunds:  "{{ route('reports.export.refunds') }}",
        support:  "{{ route('reports.export.support') }}",
        full:     "{{ route('reports.export.full') }}"
    }
};
</script>
@endsection

@push('page-js')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/js/admin-reports.js') }}"></script>
@endpush
