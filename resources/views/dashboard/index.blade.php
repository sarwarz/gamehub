@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="row g-6">

    @if(!$isFullAccess)
    {{-- ============================================================ --}}
    {{-- BASIC DASHBOARD — For non-admin internal roles               --}}
    {{-- ============================================================ --}}

    {{-- Welcome Banner --}}
    <div class="col-12">
        <div class="card bg-primary">
            <div class="d-flex align-items-center row">
                <div class="col-md-8">
                    <div class="card-body">
                        <h4 class="card-title text-white mb-1">
                            Welcome back, {{ ucwords($user->name) }}! 🎉
                        </h4>
                        <p class="text-white text-opacity-75 mb-0">
                            <span class="badge text-primary" style="background: rgba(255,255,255,.85);">{{ $user->roles->pluck('label')->implode(', ') }}</span>
                            {{ now()->format('l, F j, Y') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-center d-none d-md-block">
                    <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}"
                         height="130" loading="lazy" alt="welcome" class="mt-n3">
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    @if(isset($basicStats['total_orders']))
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-primary">
                        <i class="icon-base ti tabler-shopping-cart fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['total_orders']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Total Orders</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-warning">
                        <i class="icon-base ti tabler-clock fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['pending_orders']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Pending Orders</p>
            </div>
        </div>
    </div>
    @endif

    @if(isset($basicStats['open_tickets']))
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-info">
                        <i class="icon-base ti tabler-lifebuoy fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['open_tickets']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Open Tickets</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-danger">
                        <i class="icon-base ti tabler-urgent fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['escalated_tickets']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Escalated</p>
            </div>
        </div>
    </div>
    @endif

    @if(isset($basicStats['total_users']))
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-success">
                        <i class="icon-base ti tabler-users fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['total_users']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-primary">
                        <i class="icon-base ti tabler-user-plus fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['new_users_today']) }}</h5>
                </div>
                <p class="mb-0 text-muted">New Today</p>
            </div>
        </div>
    </div>
    @endif

    @if(isset($basicStats['unread_messages']))
    <div class="col-xl-3 col-sm-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-3 bg-label-warning">
                        <i class="icon-base ti tabler-mail fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ number_format($basicStats['unread_messages']) }}</h5>
                </div>
                <p class="mb-0 text-muted">Unread Messages</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick Actions --}}
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header pb-0">
                <h5 class="card-title mb-0"><i class="icon-base ti tabler-bolt me-2 text-warning"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(auth()->user()->hasPermission('orders'))
                    <a href="{{ route('orders.index') }}" class="btn btn-label-primary text-start">
                        <i class="icon-base ti tabler-shopping-cart me-2"></i> View Orders
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('support-tickets'))
                    <a href="{{ route('support-tickets.index') }}" class="btn btn-label-info text-start">
                        <i class="icon-base ti tabler-lifebuoy me-2"></i> Support Tickets
                    </a>
                    <a href="{{ route('support-tickets.index', ['status' => 'escalated']) }}" class="btn btn-label-danger text-start">
                        <i class="icon-base ti tabler-urgent me-2"></i> Escalated Tickets
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('users'))
                    <a href="{{ route('users.index') }}" class="btn btn-label-success text-start">
                        <i class="icon-base ti tabler-users me-2"></i> Manage Users
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('contact-messages'))
                    <a href="{{ route('contact-messages.index') }}" class="btn btn-label-warning text-start">
                        <i class="icon-base ti tabler-mail me-2"></i> Contact Messages
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders (if permission) --}}
    @if(auth()->user()->hasPermission('orders'))
    <div class="col-xl-8" id="widget-recent-orders">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Recent Orders</h5>
                    <p class="card-subtitle" id="recent-orders-subtitle">
                        <span class="placeholder-glow"><span class="placeholder col-8" style="width:150px"></span></span>
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-label-primary">View All</a>
            </div>
            <div class="card-body" id="recent-orders-body">
                @for($i = 0; $i < 5; $i++)
                <div class="d-flex mb-4 pb-1 align-items-center">
                    <div class="badge bg-label-secondary me-4 rounded p-1_5">
                        <i class="icon-base ti tabler-package icon-md"></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:80px"></span></h6>
                            <small class="text-body d-block placeholder-glow"><span class="placeholder" style="width:100px"></span></small>
                        </div>
                        <div class="user-progress">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:50px"></span></h6>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- ============================================================ --}}
    {{-- FULL DASHBOARD — For admin and superadmin only               --}}
    {{-- ============================================================ --}}

    {{-- View Sales (admin only) --}}
    <div class="col-xl-4" id="widget-view-sales">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-7">
                    <div class="card-body text-nowrap">
                        <h5 class="card-title mb-0">
                            Welcome {{ ucwords($user->name) }}! 🎉
                        </h5>
                        <p class="mb-2 placeholder-glow">
                            <span class="placeholder col-8"></span>
                        </p>
                        <h4 class="text-primary mb-1 placeholder-glow">
                            <span class="placeholder col-6"></span>
                        </h4>
                        <a href="#" class="btn btn-primary disabled placeholder col-6"></a>
                    </div>
                </div>
                <div class="col-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}"
                         height="140" loading="lazy" alt="view sales">
                </div>
            </div>
        </div>
    </div>
    {{-- ============================================================ --}}
    {{-- FULL DASHBOARD — For admin and superadmin only               --}}
    {{-- ============================================================ --}}

    {{-- Statistics --}}
    <div class="col-xl-8 col-md-12" id="widget-statistics">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">Statistics</h5>
                <small class="text-body-secondary">Updated {{ now()->startOfMonth()->diffForHumans() }}</small>
            </div>
            <div class="card-body d-flex align-items-end">
                <div class="w-100">
                    <div class="row gy-3">
                        @foreach(['Total Sales','Customers','Products','Commission'] as $label)
                        <div class="col-md-3 col-6">
                            <div class="d-flex align-items-center">
                                <div class="badge rounded bg-label-{{ ['primary','info','danger','success'][($loop->index)] }} me-4 p-2">
                                    <i class="icon-base ti tabler-{{ ['chart-pie-2','users','shopping-cart','receipt-tax'][($loop->index)] }} icon-lg"></i>
                                </div>
                                <div class="card-info">
                                    <h5 class="mb-0 placeholder-glow"><span class="placeholder col-8"></span></h5>
                                    <small>{{ $label }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Profit / Expenses / Generated Leads column --}}
    <div class="col-xxl-4 col-12">
        <div class="row g-6">

            {{-- Commission Earned --}}
            <div class="col-xl-6 col-sm-6" id="widget-profit">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h5 class="card-title mb-1">Commission Earned</h5>
                        <p class="card-subtitle">Last Month</p>
                    </div>
                    <div class="card-body">
                        <div id="profitLastMonth" style="min-height:110px">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                            <h4 class="mb-0 placeholder-glow"><span class="placeholder col-8"></span></h4>
                            <small class="placeholder-glow"><span class="placeholder col-4"></span></small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expenses --}}
            <div class="col-xl-6 col-sm-6" id="widget-expenses">
                <div class="card h-100">
                    <div class="card-header pb-2">
                        <h5 class="card-title mb-1 placeholder-glow"><span class="placeholder col-6"></span></h5>
                        <p class="card-subtitle">Seller Payouts</p>
                    </div>
                    <div class="card-body">
                        <div id="expensesChart" style="min-height:170px">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="spinner-border spinner-border-sm text-warning" role="status"></div>
                            </div>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-body-secondary placeholder-glow"><span class="placeholder col-10"></span></small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Generated Leads --}}
            <div class="col-xl-12" id="widget-generated-leads">
                <div class="card h-100">
                    <div class="card-body d-flex justify-content-between">
                        <div class="d-flex flex-column">
                            <div class="card-title mb-auto">
                                <h5 class="mb-0 text-nowrap">Sales by Category</h5>
                                <p class="mb-0">This Month</p>
                            </div>
                            <div class="chart-statistics">
                                <h3 class="card-title mb-0 placeholder-glow"><span class="placeholder col-8"></span></h3>
                                <p class="text-success text-nowrap mb-0 placeholder-glow">
                                    <span class="placeholder col-6"></span>
                                </p>
                            </div>
                        </div>
                        <div id="generatedLeadsChart" style="min-height:125px">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Revenue Report --}}
    <div class="col-xxl-8" id="widget-revenue-report">
        <div class="card h-100">
            <div class="card-body p-0">
                <div class="row row-bordered g-0">
                    <div class="col-md-8 position-relative p-6">
                        <div class="card-header d-inline-block p-0 text-wrap position-absolute">
                            <h5 class="m-0 card-title">Revenue Report</h5>
                        </div>
                        <div id="totalRevenueChart" class="mt-n1" style="min-height:413px">
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 p-4">
                        <div class="text-center mt-5">
                            <span class="btn btn-sm btn-label-primary">
                                <script>document.write(new Date().getFullYear());</script>
                            </span>
                        </div>
                        <h3 class="text-center pt-8 mb-0 placeholder-glow" id="revenue-budget-total">
                            <span class="placeholder col-6"></span>
                        </h3>
                        <p class="mb-8 text-center"><span class="fw-medium text-heading">Revenue</span></p>
                        <div class="px-3">
                            <div id="budgetChart" style="min-height:100px">
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Earning Reports --}}
    <div class="col-xxl-4 col-md-6" id="widget-earning-report">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Earnings Overview</h5>
                    <p class="card-subtitle">Weekly Performance</p>
                </div>
            </div>
            <div class="card-body pb-0">
                <ul class="p-0 m-0" id="earning-summary-list">
                    <li class="d-flex align-items-center mb-5">
                        <div class="me-4">
                            <span class="badge bg-label-primary rounded p-1_5">
                                <i class="icon-base ti tabler-chart-pie-2 icon-md"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Net Commission</h6>
                                <small class="text-body">This Week</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-4">
                                <small class="placeholder-glow"><span class="placeholder col-6" style="width:60px"></span></small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-5">
                        <div class="me-4">
                            <span class="badge bg-label-success rounded p-1_5">
                                <i class="icon-base ti tabler-currency-dollar icon-md"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Total Sales</h6>
                                <small class="text-body">Revenue</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-4">
                                <small class="placeholder-glow"><span class="placeholder col-6" style="width:60px"></span></small>
                            </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-5">
                        <div class="me-4">
                            <span class="badge bg-label-secondary text-body rounded p-1_5">
                                <i class="icon-base ti tabler-credit-card icon-md"></i>
                            </span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">Seller Payouts</h6>
                                <small class="text-body">Disbursed</small>
                            </div>
                            <div class="user-progress d-flex align-items-center gap-4">
                                <small class="placeholder-glow"><span class="placeholder col-6" style="width:60px"></span></small>
                            </div>
                        </div>
                    </li>
                </ul>
                <div id="reportBarChart" style="min-height:230px">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Popular Products --}}
    <div class="col-xxl-4 col-md-6" id="widget-popular-products">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                    <h5 class="mb-1">Popular Products</h5>
                    <p class="card-subtitle" id="popular-products-visitors">
                        <span class="placeholder-glow"><span class="placeholder col-8" style="width:100px"></span></span>
                    </p>
                </div>
            </div>
            <div class="card-body" id="popular-products-body">
                @for($i = 0; $i < 5; $i++)
                <div class="d-flex mb-6 align-items-center">
                    <div class="me-4">
                        <div class="placeholder-glow"><span class="placeholder rounded" style="width:46px;height:46px;display:inline-block"></span></div>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder col-8" style="width:120px"></span></h6>
                            <small class="text-body d-block placeholder-glow"><span class="placeholder col-6" style="width:80px"></span></small>
                        </div>
                        <div class="user-progress">
                            <p class="mb-0 placeholder-glow"><span class="placeholder col-6" style="width:60px"></span></p>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="col-xxl-4 col-md-6" id="widget-recent-orders">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Recent Orders</h5>
                    <p class="card-subtitle" id="recent-orders-subtitle">
                        <span class="placeholder-glow"><span class="placeholder col-8" style="width:150px"></span></span>
                    </p>
                </div>
            </div>
            <div class="card-body" id="recent-orders-body">
                @for($i = 0; $i < 5; $i++)
                <div class="d-flex mb-4 pb-1 align-items-center">
                    <div class="badge bg-label-secondary me-4 rounded p-1_5">
                        <i class="icon-base ti tabler-package icon-md"></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:80px"></span></h6>
                            <small class="text-body d-block placeholder-glow"><span class="placeholder" style="width:100px"></span></small>
                        </div>
                        <div class="user-progress">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:50px"></span></h6>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="col-xxl-4 col-md-6" id="widget-transactions">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                    <h5 class="mb-1">Transactions</h5>
                    <p class="card-subtitle" id="transactions-subtitle">
                        <span class="placeholder-glow"><span class="placeholder col-8" style="width:200px"></span></span>
                    </p>
                </div>
            </div>
            <div class="card-body" id="transactions-body">
                @for($i = 0; $i < 6; $i++)
                <div class="d-flex mb-3 pb-1 align-items-center">
                    <div class="badge bg-label-secondary me-4 rounded p-1_5">
                        <i class="icon-base ti tabler-wallet icon-md"></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:90px"></span></h6>
                            <small class="text-body d-block placeholder-glow"><span class="placeholder" style="width:70px"></span></small>
                        </div>
                        <div class="user-progress">
                            <h6 class="mb-0 placeholder-glow"><span class="placeholder" style="width:50px"></span></h6>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Invoices --}}
    <div class="col-xxl-8" id="widget-invoices">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Recent Invoices</h5>
                    <p class="card-subtitle" id="invoices-subtitle">
                        <span class="placeholder-glow"><span class="placeholder" style="width:180px"></span></span>
                    </p>
                </div>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-label-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" id="invoices-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Issued</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="invoices-tbody">
                        @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td><span class="placeholder-glow"><span class="placeholder" style="width:70px"></span></span></td>
                            <td><span class="placeholder-glow"><span class="placeholder" style="width:100px"></span></span></td>
                            <td><span class="placeholder-glow"><span class="placeholder" style="width:60px"></span></span></td>
                            <td><span class="placeholder-glow"><span class="placeholder" style="width:80px"></span></span></td>
                            <td><span class="placeholder-glow"><span class="placeholder" style="width:50px"></span></span></td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif {{-- End @if(!$isFullAccess) / @else --}}

</div>
@endsection

@push('page-js')
@if($isFullAccess)
<script>
    window._dashboardWidgetUrls = {
        statistics:          '{{ route("dashboard.widgets.statistics") }}',
        viewSales:           '{{ route("dashboard.widgets.view-sales") }}',
        profit:              '{{ route("dashboard.widgets.profit") }}',
        expenses:            '{{ route("dashboard.widgets.expenses") }}',
        revenueReport:       '{{ route("dashboard.widgets.revenue-report") }}',
        earningReport:       '{{ route("dashboard.widgets.earning-report") }}',
        popularProducts:     '{{ route("dashboard.widgets.popular-products") }}',
        recentOrders:        '{{ route("dashboard.widgets.recent-orders") }}',
        recentTransactions:  '{{ route("dashboard.widgets.recent-transactions") }}',
        generatedLeads:      '{{ route("dashboard.widgets.generated-leads") }}',
        invoices:            '{{ route("dashboard.widgets.invoices") }}',
    };
</script>
<script src="{{ asset('assets/js/dashboard-widgets.js') }}"></script>
@else
<script>
    window._dashboardWidgetUrls = {};
    @if(auth()->user()->hasPermission('orders'))
    window._dashboardWidgetUrls.recentOrders = '{{ route("dashboard.widgets.recent-orders") }}';
    @endif
</script>
@if(auth()->user()->hasPermission('orders'))
<script src="{{ asset('assets/js/dashboard-widgets.js') }}"></script>
@endif
@endif
@endpush
