@extends('layouts.app')
@section('title', $seller->store_name . ' – Seller Details')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<style>
    .stat-card { transition: transform .15s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .nav-tabs .nav-link.active { font-weight: 600; }
</style>
@endpush

@section('content')
<div class="app-ecommerce">

    {{-- ══════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-6 gap-4">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $seller->logo ? asset($seller->logo) : asset('assets/img/avatars/1.png') }}"
                 class="rounded-circle shadow-sm" width="64" height="64" alt="Logo">

            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="mb-0">{{ $seller->store_name }}</h4>

                    @if($seller->is_verified)
                        <span class="badge bg-label-success"><i class="ti tabler-circle-check me-1"></i>Verified</span>
                    @else
                        <span class="badge bg-label-warning">Unverified</span>
                    @endif

                    <span class="badge bg-{{ match($seller->status) {
                        'active' => 'success', 'pending' => 'warning', 'suspended' => 'danger', default => 'secondary'
                    } }}">{{ ucfirst($seller->status) }}</span>
                </div>

                <div class="text-muted">
                    <span><i class="ti tabler-user me-1"></i>{{ $seller->user?->name ?? '-' }}</span>
                    <span class="mx-2">|</span>
                    <span><i class="ti tabler-mail me-1"></i>{{ $seller->email ?? $seller->user?->email ?? '-' }}</span>
                    <span class="mx-2">|</span>
                    <span><i class="ti tabler-calendar me-1"></i>Joined {{ $seller->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('sellers.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('sellers.edit', $seller->id) }}" class="btn btn-primary">
                <i class="ti tabler-edit me-1"></i> Edit Seller
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         FINANCIAL STATS CARDS
    ══════════════════════════════════════════ --}}
    <div class="row mb-6 g-4">
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-wallet"></i></span>
                    </div>
                    <h5 class="mb-0">{{ format_currency($balance->available_balance) }}</h5>
                    <small class="text-muted">Available Balance</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-clock"></i></span>
                    </div>
                    <h5 class="mb-0">{{ format_currency($balance->pending_balance) }}</h5>
                    <small class="text-muted">Pending Balance</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-chart-bar"></i></span>
                    </div>
                    <h5 class="mb-0">{{ format_currency($balance->total_earned) }}</h5>
                    <small class="text-muted">Total Earned</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-cash"></i></span>
                    </div>
                    <h5 class="mb-0">{{ format_currency($balance->total_paid) }}</h5>
                    <small class="text-muted">Total Withdrawn</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-secondary"><i class="ti tabler-shopping-cart"></i></span>
                    </div>
                    <h5 class="mb-0">{{ $stats['total_orders'] }}</h5>
                    <small class="text-muted">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body text-center">
                    <div class="avatar mx-auto mb-2">
                        <span class="avatar-initial rounded bg-label-danger"><i class="ti tabler-package"></i></span>
                    </div>
                    <h5 class="mb-0">{{ $stats['active_offers'] }} / {{ $stats['total_offers'] }}</h5>
                    <small class="text-muted">Active / Total Offers</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         TABS
    ══════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header p-0 pt-2 px-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview">
                        <i class="ti tabler-info-circle me-1"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-earnings" id="earnings-tab-link">
                        <i class="ti tabler-coin me-1"></i> Earnings
                        <span class="badge bg-warning ms-1">{{ $stats['pending_earnings'] }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-withdrawals" id="withdrawals-tab-link">
                        <i class="ti tabler-cash me-1"></i> Withdrawals
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-offers" id="offers-tab-link">
                        <i class="ti tabler-tag me-1"></i> Offers
                        <span class="badge bg-success ms-1">{{ $stats['active_offers'] }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body tab-content pt-3">

            {{-- ── TAB: OVERVIEW ── --}}
            <div class="tab-pane fade show active" id="tab-overview">
                <div class="row g-4">
                    {{-- Seller Info --}}
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted mb-3">Store Information</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" width="160">Store Name</td><td class="fw-medium">{{ $seller->store_name }}</td></tr>
                            <tr><td class="text-muted">Slug</td><td><code>{{ $seller->slug }}</code></td></tr>
                            <tr><td class="text-muted">Rating</td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="ti tabler-star{{ $i <= round($seller->rating ?? 0) ? '-filled text-warning' : ' text-muted' }}"></i>
                                    @endfor
                                    <span class="ms-1">({{ $seller->display_rating }})</span>
                                </td>
                            </tr>
                            <tr><td class="text-muted">Total Sales</td><td>{{ number_format($seller->total_sales) }}</td></tr>
                            @if($seller->description)
                                <tr><td class="text-muted">Description</td><td>{{ Str::limit($seller->description, 200) }}</td></tr>
                            @endif
                        </table>
                    </div>

                    {{-- Contact & Business --}}
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted mb-3">Contact & Business</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted" width="160">Email</td><td>{{ $seller->email ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Phone</td><td>{{ $seller->phone ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Website</td><td>{{ $seller->website ? Str::limit($seller->website, 40) : '-' }}</td></tr>
                            <tr><td class="text-muted">Company</td><td>{{ $seller->company_name ?? '-' }}</td></tr>
                            <tr><td class="text-muted">VAT / Tax ID</td><td>{{ $seller->vat_number ?? $seller->tax_id ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Location</td>
                                <td>
                                    {{ collect([$seller->address, $seller->city, $seller->state, $seller->country])->filter()->join(', ') ?: '-' }}
                                    @if($seller->postal_code) ({{ $seller->postal_code }}) @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- Balance Breakdown --}}
                    <div class="col-12">
                        <hr>
                        <h6 class="text-uppercase text-muted mb-3">Financial Summary</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="p-3 rounded bg-label-success">
                                    <small class="text-muted d-block">Available</small>
                                    <h5 class="mb-0">{{ format_currency($balance->available_balance) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded bg-label-warning">
                                    <small class="text-muted d-block">Pending (In Escrow)</small>
                                    <h5 class="mb-0">{{ format_currency($balance->pending_balance) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded bg-label-primary">
                                    <small class="text-muted d-block">Lifetime Earned</small>
                                    <h5 class="mb-0">{{ format_currency($balance->total_earned) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 rounded bg-label-info">
                                    <small class="text-muted d-block">Total Paid Out</small>
                                    <h5 class="mb-0">{{ format_currency($balance->total_paid) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── TAB: EARNINGS ── --}}
            <div class="tab-pane fade" id="tab-earnings">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="earnings-table" width="100%">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Product</th>
                                <th>Gross</th>
                                <th>Commission</th>
                                <th>Net Earning</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            {{-- ── TAB: WITHDRAWALS ── --}}
            <div class="tab-pane fade" id="tab-withdrawals">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="withdrawals-table" width="100%">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            {{-- ── TAB: OFFERS ── --}}
            <div class="tab-pane fade" id="tab-offers">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="offers-table" width="100%">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Available Keys</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('page-js')
<script>
$(function () {

    const showUrl = '{{ route('sellers.show', $seller->id) }}';
    let earningsLoaded = false, withdrawalsLoaded = false, offersLoaded = false;

    // Lazy-load DataTables when the tab is first shown
    $('#earnings-tab-link').on('shown.bs.tab', function () {
        if (earningsLoaded) return;
        earningsLoaded = true;
        $('#earnings-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: showUrl + '?table=earnings',
            columns: [
                { data: 'order_number' },
                { data: 'product' },
                { data: 'gross', className: 'text-end' },
                { data: 'commission_amount', className: 'text-end' },
                { data: 'net', className: 'text-end fw-bold' },
                { data: 'status_badge', className: 'text-center', orderable: false, searchable: false },
                { data: 'date' }
            ],
            order: [[6, 'desc']]
        });
    });

    $('#withdrawals-tab-link').on('shown.bs.tab', function () {
        if (withdrawalsLoaded) return;
        withdrawalsLoaded = true;
        $('#withdrawals-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: showUrl + '?table=withdrawals',
            columns: [
                { data: 'amount_fmt', className: 'text-end' },
                { data: 'method_fmt' },
                { data: 'status_badge', className: 'text-center', orderable: false, searchable: false },
                { data: 'note', defaultContent: '-' },
                { data: 'date' }
            ],
            order: [[4, 'desc']]
        });
    });

    $('#offers-tab-link').on('shown.bs.tab', function () {
        if (offersLoaded) return;
        offersLoaded = true;
        $('#offers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: showUrl + '?table=offers',
            columns: [
                { data: 'product_col', orderable: false },
                { data: 'price', className: 'text-end' },
                { data: 'keys_count', className: 'text-center' },
                { data: 'status_badge', className: 'text-center', orderable: false, searchable: false },
                { data: 'date' }
            ],
            order: [[4, 'desc']]
        });
    });

});
</script>
@endpush
