@extends('layouts.app')
@section('title', 'Order #' . $order->order_number)

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
.order-badge { font-size: 0.68rem; letter-spacing: .3px; }
.stat-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; border-radius: .5rem; }
.order-card { border: 0; border-radius: .75rem; box-shadow: 0 2px 6px rgba(0,0,0,.04); margin-bottom: 1.25rem; transition: box-shadow .2s; }
.order-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
.order-card .card-header { border-bottom: 1px solid #f0f0f3; padding: 1rem 1.25rem; background: transparent; }
.order-card .card-header h5 { margin: 0; font-size: .9375rem; font-weight: 600; }
.order-card .card-body { padding: 1.25rem !important; }
.order-header { background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%); border-radius: .75rem; padding: 1.5rem 2rem; margin-bottom: 1.5rem; color: #fff; }
.order-header h4 { font-weight: 700; font-size: 1.25rem; margin: 0 0 .25rem; }
.order-header p { margin: 0; opacity: .85; font-size: .875rem; }
.order-header .header-icon { width: 52px; height: 52px; border-radius: .75rem; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.order-stat-card { border: 0; border-radius: .625rem; padding: 1rem 1.25rem; display: flex; align-items: center; gap: .875rem; }
.order-stat-card .stat-icon { width: 44px; height: 44px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.timeline-compact .timeline-item { padding-bottom: .5rem; }
.timeline-compact .timeline-event { padding-bottom: .25rem; }
.sidebar-card .card-header { padding: .875rem 1.125rem; }
.sidebar-card .card-body { padding: 1rem 1.125rem !important; }
.resend-dropdown-item:hover { background: rgba(115,103,240,.08); }
.copy-btn { cursor: pointer; transition: color .15s; }
.copy-btn:hover { color: #7367f0 !important; }
.delivery-key-box { background: #f8f7fa; border-radius: .375rem; padding: .375rem .5rem; font-family: ui-monospace, monospace; font-size: .75rem; word-break: break-all; }
.product-img-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: .5rem; border: 1px solid #f0f0f3; }
.action-dropdown .dropdown-menu { min-width: 220px; }
.quick-info-row { display: flex; align-items: center; gap: .5rem; padding: .5rem 0; border-bottom: 1px solid #f5f5f9; }
.quick-info-row:last-child { border-bottom: 0; }
.quick-info-row .qi-label { font-size: .75rem; color: #a1acb8; min-width: 90px; }
.quick-info-row .qi-value { font-size: .8125rem; font-weight: 500; }
</style>
@endpush

@section('content')
<div class="app-ecommerce-order-edit">
    @include('partials.alerts')

    @php
        $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'refunded' => 'secondary', 'cancelled' => 'danger'];
        $payStatusColors = ['pending' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary'];
        $walletMeta = $order->meta['wallet'] ?? null;
        $couponMeta = $order->meta['coupon'] ?? null;
        $taxDetails = $order->meta['tax_details'] ?? [];
        $clientMeta = $order->meta['client'] ?? [];
        $flags      = $order->meta['flags'] ?? [];

        $orderCurrency = $order->currency;
        $baseCurrency  = $order->base_currency ?? $orderCurrency;
        $isMultiCurrency = $orderCurrency !== $baseCurrency && $order->exchange_rate && $order->exchange_rate != 1;
    @endphp

    {{-- Page Header --}}
    <div class="order-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon"><i class="ti tabler-receipt"></i></div>
            <div>
                <h4>
                    Order #{{ $order->order_number }}
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} ms-2" style="font-size:.7rem;vertical-align:middle">{{ ucfirst($order->status) }}</span>
                    <span class="badge bg-{{ $payStatusColors[$order->payment_status] ?? 'secondary' }} ms-2" style="font-size:.7rem;vertical-align:middle">{{ ucfirst($order->payment_status) }}</span>
                </h4>
                <p>
                    <i class="ti tabler-calendar ti-xs me-1"></i> {{ $order->created_at->format('M d, Y h:i A') }}
                    @if($order->payment_method)
                        <span class="mx-1">·</span>
                        <i class="ti tabler-credit-card ti-xs me-1"></i> {{ ucfirst($order->payment_method) }}
                    @endif
                    @if($order->currency)
                        <span class="mx-1">·</span> {{ $order->currency }}
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Actions dropdown --}}
            <div class="dropdown action-dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ti tabler-dots-vertical ti-xs me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item resend-dropdown-item" onclick="resendNotification('order_placed')">
                            <i class="ti tabler-mail-forward ti-xs me-2 text-primary"></i> Resend Order Placed Email
                        </a>
                    </li>
                    @if($order->payment_status === 'paid')
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item resend-dropdown-item" onclick="resendNotification('payment_confirmed')">
                            <i class="ti tabler-receipt ti-xs me-2 text-success"></i> Resend Payment Confirmed
                        </a>
                    </li>
                    @endif
                    @if($order->status === 'completed')
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item resend-dropdown-item" onclick="resendNotification('order_completed')">
                            <i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Resend Delivery Confirmation
                        </a>
                    </li>
                    @endif
                    @if($order->invoice)
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item resend-dropdown-item" onclick="resendNotification('invoice')">
                            <i class="ti tabler-file-invoice ti-xs me-2 text-info"></i> Resend Invoice Email
                        </a>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Order</h6></li>
                    @if(!$order->invoice && $order->payment_status === 'paid')
                    <li>
                        <form method="POST" action="{{ route('invoices.generate', $order->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="ti tabler-file-plus ti-xs me-2 text-primary"></i> Generate Invoice
                            </button>
                        </form>
                    </li>
                    @endif
                    @if($order->invoice)
                    <li>
                        <a href="{{ route('invoices.download', $order->invoice->id) }}" class="dropdown-item">
                            <i class="ti tabler-download ti-xs me-2 text-success"></i> Download Invoice PDF
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('invoices.show', $order->invoice->id) }}" target="_blank" class="dropdown-item">
                            <i class="ti tabler-printer ti-xs me-2"></i> Print Invoice
                        </a>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a href="javascript:void(0)" class="dropdown-item text-danger" onclick="deleteOrder({{ $order->id }})">
                            <i class="ti tabler-trash ti-xs me-2"></i> Delete Order
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('orders.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left ti-xs me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="order-stat-card bg-label-primary">
                <div class="stat-icon bg-primary text-white"><i class="ti tabler-currency-dollar"></i></div>
                <div>
                    <div class="text-muted small">Total</div>
                    <div class="fw-bold fs-5">{{ format_currency($order->total_amount, $orderCurrency) }}</div>
                    @if($isMultiCurrency)
                        <div class="text-muted" style="font-size:.7rem">Base: {{ format_currency($order->base_total_amount) }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="order-stat-card bg-label-success">
                <div class="stat-icon bg-success text-white"><i class="ti tabler-package"></i></div>
                <div>
                    <div class="text-muted small">Items</div>
                    <div class="fw-bold fs-5">{{ $order->items->sum('quantity') }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="order-stat-card bg-label-info">
                <div class="stat-icon bg-info text-white"><i class="ti tabler-users"></i></div>
                <div>
                    <div class="text-muted small">Sellers</div>
                    <div class="fw-bold fs-5">{{ $order->items->pluck('seller_id')->unique()->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="order-stat-card bg-label-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                <div class="stat-icon bg-{{ $statusColors[$order->status] ?? 'secondary' }} text-white"><i class="ti tabler-truck-delivery"></i></div>
                <div>
                    <div class="text-muted small">Delivery</div>
                    @php
                        $totalDeliveries = $order->items->flatMap->deliveries->count();
                        $deliveredCount  = $order->items->flatMap->deliveries->where('status', 'delivered')->count();
                    @endphp
                    <div class="fw-bold fs-5">{{ $deliveredCount }}/{{ $totalDeliveries }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN (9) --}}
        <div class="col-lg-9">

            {{-- Order Items --}}
            <div class="card order-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti tabler-package ti-md me-2 text-primary"></i> Order Items</h5>
                    <span class="badge bg-label-primary rounded-pill">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:45%">Product</th>
                                <th>Seller</th>
                                <th class="text-center" width="60">Qty</th>
                                <th class="text-end" width="110">Unit Price</th>
                                <th class="text-end" width="120">Subtotal</th>
                                <th class="text-center" width="90">Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $item->product->image ? asset($item->product->image) : asset('assets/img/placeholder/product.png') }}"
                                             alt="{{ $item->product->title }}" class="product-img-thumb">
                                        <div>
                                            <span class="fw-semibold d-block">{{ Str::limit($item->product->title, 40) }}</span>
                                            @if($item->earning)
                                            <div class="mt-1">
                                                <span class="badge bg-label-success order-badge">Net {{ format_currency($item->earning->net_amount) }}</span>
                                                <span class="badge bg-label-danger order-badge">Fee {{ format_currency($item->earning->commission) }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->seller)
                                        <a href="{{ route('sellers.show', $item->seller->id) }}" class="text-body fw-medium">
                                            {{ Str::limit($item->seller->store_name, 20) }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ format_currency($item->unit_price, $orderCurrency) }}</td>
                                <td class="text-end fw-semibold">{{ format_currency($item->subtotal, $orderCurrency) }}</td>
                                <td class="text-center">
                                    @php $itemDelivery = $item->deliveries->first(); @endphp
                                    @if($itemDelivery)
                                        <span class="badge bg-label-{{ $itemDelivery->status === 'delivered' ? 'success' : ($itemDelivery->status === 'failed' ? 'danger' : 'warning') }} order-badge">
                                            {{ ucfirst($itemDelivery->status) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-end">
                        <div style="min-width:300px">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-semibold">{{ format_currency($order->subtotal, $orderCurrency) }}</span>
                            </div>
                            @if($order->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    Tax
                                    @if(count($taxDetails))
                                        <i class="ti tabler-info-circle ti-xs ms-1" data-bs-toggle="tooltip" title="@foreach($taxDetails as $td){{ $td['name'] }}: {{ format_currency($td['amount'], $orderCurrency) }}{{ !$loop->last ? ', ' : '' }}@endforeach"></i>
                                    @endif
                                </span>
                                <span>+{{ format_currency($order->tax_amount, $orderCurrency) }}</span>
                            </div>
                            @endif
                            @if($order->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">
                                    Discount
                                    @if($couponMeta)
                                        <span class="badge bg-label-primary order-badge ms-1">{{ $couponMeta['code'] }}</span>
                                    @endif
                                </span>
                                <span class="text-danger">-{{ format_currency($order->discount_amount, $orderCurrency) }}</span>
                            </div>
                            @endif
                            @if($walletMeta && ($walletMeta['wallet_amount'] ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="ti tabler-wallet ti-xs me-1"></i> Wallet Paid</span>
                                <span class="text-success">-{{ format_currency($walletMeta['wallet_amount'], $orderCurrency) }}</span>
                            </div>
                            @if(($walletMeta['gateway_amount'] ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><i class="ti tabler-credit-card ti-xs me-1"></i> Gateway Due</span>
                                <span>{{ format_currency($walletMeta['gateway_amount'], $orderCurrency) }}</span>
                            </div>
                            @endif
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-primary fs-5">{{ format_currency($order->total_amount, $orderCurrency) }}</span>
                            </div>
                            @if($isMultiCurrency)
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-muted small">Base ({{ $baseCurrency }})</span>
                                <span class="text-muted small">{{ format_currency($order->base_total_amount) }} <span style="font-size:.65rem">× {{ $order->exchange_rate }}</span></span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery Details --}}
            @if($order->items->flatMap->deliveries->isNotEmpty())
            <div class="card order-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti tabler-key ti-md me-2 text-primary"></i> Delivery & Keys</h5>
                    <span class="badge bg-label-{{ $deliveredCount === $totalDeliveries && $totalDeliveries > 0 ? 'success' : 'warning' }} rounded-pill">
                        {{ $deliveredCount }}/{{ $totalDeliveries }} delivered
                    </span>
                </div>
                <div class="card-body p-0">
                    @php $dIndex = 0; @endphp
                    @foreach($order->items as $item)
                    @foreach($item->deliveries as $delivery)
                    @php $dIndex++; @endphp
                    <div class="p-3 {{ !$loop->last || !$loop->parent->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-label-{{ $delivery->status === 'delivered' ? 'success' : ($delivery->status === 'failed' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($delivery->status) }}
                                </span>
                                <span class="fw-semibold small">{{ Str::limit($item->product->title, 35) }}</span>
                                <span class="text-muted small">× {{ $item->quantity }}</span>
                            </div>
                            <div class="d-flex gap-2">
                                @if($delivery->status === 'failed')
                                <form method="POST" action="{{ route('admin.deliveries.retry', $delivery->id) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-label-warning"><i class="ti tabler-refresh ti-xs me-1"></i> Retry</button>
                                </form>
                                @endif
                                @if($delivery->delivered_at)
                                <span class="text-muted small align-self-center">
                                    <i class="ti tabler-clock ti-xs me-1"></i>{{ $delivery->delivered_at->format('M d, h:i A') }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @if($delivery->status === 'delivered' && isset($delivery->payload['keys']))
                        <div class="row g-2 mt-1">
                            @foreach($delivery->payload['keys'] as $ki => $key)
                            <div class="col-md-6">
                                <div class="delivery-key-box d-flex justify-content-between align-items-center">
                                    <span>{{ $key }}</span>
                                    <i class="ti tabler-copy ti-xs text-muted copy-btn" title="Copy key" onclick="copyKey(this, '{{ addslashes($key) }}')"></i>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @elseif($delivery->status === 'delivered' && isset($delivery->payload['download_link']))
                        <a href="{{ $delivery->payload['download_link'] }}" target="_blank" class="btn btn-xs btn-outline-primary mt-1">
                            <i class="ti tabler-download ti-xs me-1"></i> Download File
                        </a>
                        @elseif($delivery->status === 'failed')
                        <div class="text-danger small mt-1"><i class="ti tabler-alert-circle ti-xs me-1"></i>{{ $delivery->payload['error'] ?? 'Delivery failed' }}</div>
                        @endif
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Transactions --}}
            @if($order->transactions->count())
            <div class="card order-card">
                <div class="card-header">
                    <h5><i class="ti tabler-arrows-exchange ti-md me-2 text-primary"></i> Transactions</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>TRX ID</th>
                                <th>Method</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->transactions as $txn)
                            <tr>
                                <td>
                                    <code class="small copy-btn" title="Copy TRX" onclick="copyKey(this, '{{ $txn->trx }}')">{{ $txn->trx }}</code>
                                </td>
                                <td>
                                    @php $icons = ['wallet' => 'tabler-wallet', 'stripe' => 'tabler-brand-stripe', 'paypal' => 'tabler-brand-paypal']; @endphp
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="ti {{ $icons[strtolower($txn->payment_method ?? '')] ?? 'tabler-credit-card' }} ti-xs text-muted"></i>
                                        {{ ucfirst($txn->payment_method ?? '—') }}
                                    </span>
                                </td>
                                <td><span class="badge bg-label-{{ $txn->type === 'credit' ? 'success' : 'info' }} order-badge">{{ strtoupper($txn->type) }}</span></td>
                                <td class="text-end fw-semibold">{{ format_currency($txn->amount, $orderCurrency) }}</td>
                                <td class="text-center"><span class="badge bg-label-{{ $txn->status === 'completed' ? 'success' : ($txn->status === 'reversed' ? 'danger' : 'warning') }} order-badge">{{ ucfirst($txn->status) }}</span></td>
                                <td class="text-muted small">{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Order History Timeline --}}
            <div class="card order-card">
                <div class="card-header">
                    <h5><i class="ti tabler-history ti-md me-2 text-primary"></i> Order Timeline</h5>
                </div>
                <div class="card-body pt-3">
                    <ul class="timeline pb-0 mb-0 timeline-compact">
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-primary"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Order Placed</h6>
                                    <small class="text-muted">{{ $order->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mt-1 mb-0 small text-muted">
                                    Order #{{ $order->order_number }} created
                                    @if($flags['manual_order'] ?? false) <span class="badge bg-label-info order-badge ms-1">Admin</span> @endif
                                    via {{ ucfirst($clientMeta['platform'] ?? 'web') }}
                                </p>
                            </div>
                        </li>

                        @if($order->paid_at)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Payment Confirmed</h6>
                                    <small class="text-muted">{{ $order->paid_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mt-1 mb-0 small text-muted">{{ ucfirst($order->payment_method) }} — {{ format_currency($order->total_amount, $orderCurrency) }}</p>
                            </div>
                        </li>
                        @endif

                        @foreach($order->transactions->where('status', '!=', 'completed')->where('status', '!=', 'pending') as $txn)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-{{ $txn->status === 'reversed' ? 'danger' : 'warning' }}"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Transaction {{ ucfirst($txn->status) }}</h6>
                                    <small class="text-muted">{{ $txn->created_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mt-1 mb-0 small text-muted">{{ $txn->trx }} — {{ format_currency($txn->amount, $orderCurrency) }}</p>
                            </div>
                        </li>
                        @endforeach

                        @if($order->invoice)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-info"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Invoice Generated</h6>
                                    <small class="text-muted">{{ $order->invoice->issued_at->format('M d, Y h:i A') }}</small>
                                </div>
                                <p class="mt-1 mb-0 small">
                                    <a href="{{ route('invoices.show', $order->invoice->id) }}" target="_blank" class="text-primary">#{{ $order->invoice->invoice_number }}</a>
                                </p>
                            </div>
                        </li>
                        @endif

                        @foreach($order->items as $item)
                        @foreach($item->deliveries->where('status', 'delivered') as $delivery)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Keys Delivered</h6>
                                    <small class="text-muted">{{ $delivery->delivered_at?->format('M d, Y h:i A') ?? '' }}</small>
                                </div>
                                <p class="mt-1 mb-0 small text-muted">{{ Str::limit($item->product->title, 40) }} × {{ $item->quantity }}</p>
                            </div>
                        </li>
                        @endforeach
                        @foreach($item->deliveries->where('status', 'failed') as $delivery)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-danger"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Delivery Failed</h6>
                                    <small class="text-muted">{{ $delivery->updated_at?->format('M d, Y h:i A') ?? '' }}</small>
                                </div>
                                <p class="mt-1 mb-0 small text-danger">{{ $delivery->payload['error'] ?? 'Auto-delivery failed' }}</p>
                            </div>
                        </li>
                        @endforeach
                        @endforeach

                        @if($order->completed_at)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-success"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Order Completed</h6>
                                    <small class="text-muted">{{ $order->completed_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif

                        @if($order->cancelled_at)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-danger"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Order Cancelled</h6>
                                    <small class="text-muted">{{ $order->cancelled_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif

                        @if($order->refunded_at)
                        <li class="timeline-item timeline-item-transparent border-primary">
                            <span class="timeline-point timeline-point-secondary"></span>
                            <div class="timeline-event">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Order Refunded</h6>
                                    <small class="text-muted">{{ $order->refunded_at->format('M d, Y h:i A') }}</small>
                                </div>
                            </div>
                        </li>
                        @endif

                        <li class="timeline-item timeline-item-transparent border-dashed pb-0">
                            <span class="timeline-point timeline-point-{{ $statusColors[$order->status] ?? 'secondary' }}"></span>
                            <div class="timeline-event pb-0">
                                <div class="timeline-header">
                                    <h6 class="mb-0">Current: {{ ucfirst($order->status) }}</h6>
                                    <small class="text-muted">{{ now()->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Order Notes --}}
            <div class="card order-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti tabler-notes ti-md me-2 text-primary"></i> Notes & Activity</h5>
                    <span class="badge bg-label-secondary rounded-pill">{{ $order->notes->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3" style="max-height: 400px; overflow-y: auto">
                        @forelse($order->notes as $note)
                        <div class="d-flex gap-3 mb-3">
                            <div class="avatar avatar-sm flex-shrink-0 mt-1">
                                <span class="avatar-initial rounded-circle bg-label-{{ $note->type === 'system' ? 'secondary' : 'primary' }}">
                                    <i class="ti {{ $note->type === 'system' ? 'tabler-settings' : 'tabler-user' }} ti-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="fw-semibold small">{{ $note->user?->name ?? 'System' }}</span>
                                        @if($note->is_visible_to_customer)
                                            <span class="badge bg-label-info order-badge ms-1">Visible to customer</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                        @if(auth()->user()->can('delete', $note))
                                        <form method="POST" action="{{ route('orders.notes.destroy', $note->id) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-link btn-sm p-0 text-danger" title="Delete"><i class="ti tabler-x ti-xs"></i></button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="mb-0 small mt-1 {{ $note->type === 'system' ? 'text-muted' : '' }}">{{ $note->note }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3">
                            <i class="ti tabler-notes-off ti-xl text-muted d-block mb-2"></i>
                            <p class="text-muted small mb-0">No notes yet</p>
                        </div>
                        @endforelse
                    </div>
                    <hr>
                    <form method="POST" action="{{ route('orders.notes.store', $order->id) }}">
                        @csrf
                        <input type="hidden" name="visibility" value="private" id="noteVisibilityHidden">
                        <textarea name="note" class="form-control mb-2" rows="2" placeholder="Add a note..." required></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="noteVisibility"
                                           onchange="document.getElementById('noteVisibilityHidden').value = this.checked ? 'customer' : 'private'; document.getElementById('noteEmailHint').classList.toggle('d-none', !this.checked)">
                                    <label class="form-check-label small" for="noteVisibility">Visible to customer</label>
                                </div>
                                <div class="d-none small text-primary mt-1" id="noteEmailHint">
                                    <i class="ti tabler-mail ti-xs me-1"></i> Email will be sent to customer
                                </div>
                            </div>
                            <button class="btn btn-sm btn-primary"><i class="ti tabler-send ti-xs me-1"></i> Add Note</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- RIGHT SIDEBAR (3) --}}
        <div class="col-lg-3">

            {{-- Update Status --}}
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-toggle-left ti-md me-2 text-primary"></i> Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('orders.update', $order->id) }}">
                        @csrf
                        @method('PUT')
                        <label class="form-label small fw-semibold">Order Status</label>
                        <select name="status" class="form-select form-select-sm mb-3">
                            @foreach(['pending','processing','completed','refunded','cancelled'] as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="ti tabler-check ti-xs me-1"></i> Update
                        </button>
                    </form>
                </div>
            </div>

            {{-- Order Info --}}
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-info-circle ti-md me-2 text-primary"></i> Order Info</h5>
                </div>
                <div class="card-body pt-2 pb-2">
                    <div class="quick-info-row">
                        <span class="qi-label">Order #</span>
                        <span class="qi-value">{{ $order->order_number }}</span>
                    </div>
                    <div class="quick-info-row">
                        <span class="qi-label">Placed</span>
                        <span class="qi-value">{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="quick-info-row">
                        <span class="qi-label">Payment</span>
                        <span class="qi-value">{{ ucfirst($order->payment_method ?? '—') }}</span>
                    </div>
                    <div class="quick-info-row">
                        <span class="qi-label">Currency</span>
                        <span class="qi-value">
                            {{ $order->currency }}
                            @if($isMultiCurrency)
                                <span class="text-muted" style="font-size:.7rem">(base: {{ $baseCurrency }})</span>
                            @endif
                        </span>
                    </div>
                    @if($isMultiCurrency)
                    <div class="quick-info-row">
                        <span class="qi-label">Rate</span>
                        <span class="qi-value">1 {{ $baseCurrency }} = {{ $order->exchange_rate }} {{ $orderCurrency }}</span>
                    </div>
                    @endif
                    @if($order->paid_at)
                    <div class="quick-info-row">
                        <span class="qi-label">Paid At</span>
                        <span class="qi-value">{{ $order->paid_at->format('M d, h:i A') }}</span>
                    </div>
                    @endif
                    @if($clientMeta['ip'] ?? null)
                    <div class="quick-info-row">
                        <span class="qi-label">IP Address</span>
                        <span class="qi-value"><code class="small">{{ $clientMeta['ip'] }}</code></span>
                    </div>
                    @endif
                    @if($clientMeta['platform'] ?? null)
                    <div class="quick-info-row">
                        <span class="qi-label">Platform</span>
                        <span class="qi-value">{{ ucfirst($clientMeta['platform']) }}</span>
                    </div>
                    @endif
                    @if($flags['manual_order'] ?? false)
                    <div class="quick-info-row">
                        <span class="qi-label">Source</span>
                        <span class="qi-value"><span class="badge bg-label-info order-badge">Admin Created</span></span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Customer --}}
            <div class="card order-card sidebar-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti tabler-user ti-md me-2 text-primary"></i> Customer</h5>
                    @if($order->user)
                        <a href="{{ route('users.edit', $order->user->id) }}" class="btn btn-xs btn-label-primary"><i class="ti tabler-pencil ti-xs"></i></a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $order->user?->avatar_url ?? asset('assets/img/avatars/1.png') }}"
                             class="rounded-circle" width="40" height="40" style="object-fit:cover" alt="">
                        <div>
                            @if($order->user)
                                <a href="{{ route('users.show', $order->user->id) }}" class="fw-semibold text-body d-block small">{{ $order->user->name }}</a>
                                <small class="text-muted">#{{ $order->user->id }}</small>
                            @else
                                <span class="fw-semibold d-block small">Guest</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-2">
                        <i class="ti tabler-mail ti-xs text-muted me-1"></i>
                        <small>{{ $order->user?->email ?? '—' }}</small>
                    </div>
                    @if($order->user)
                    <div class="d-flex gap-2 mt-3">
                        <div class="flex-grow-1 text-center p-2 rounded bg-label-primary">
                            <div class="fw-bold">{{ $order->user->orders()->count() }}</div>
                            <div class="text-muted" style="font-size:.65rem">Orders</div>
                        </div>
                        <div class="flex-grow-1 text-center p-2 rounded bg-label-success">
                            <div class="fw-bold">{{ format_currency($order->user->orders()->where('payment_status', 'paid')->sum('total_amount')) }}</div>
                            <div class="text-muted" style="font-size:.65rem">Spent</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Billing Address --}}
            @if($billingAddress)
            <div class="card order-card sidebar-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="ti tabler-map-pin ti-md me-2 text-primary"></i> Billing</h5>
                    @if(!in_array($order->status, ['completed','refunded']))
                    <a href="javascript:void(0)" class="btn btn-xs btn-label-primary" data-bs-toggle="modal" data-bs-target="#editBillingAddress">
                        <i class="ti tabler-pencil ti-xs"></i>
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <span class="fw-semibold">{{ $billingAddress->name }}</span><br>
                        {{ $billingAddress->address }}<br>
                        {{ $billingAddress->city }}@if($billingAddress->state), {{ $billingAddress->state }}@endif
                        @if($billingAddress->postal_code) {{ $billingAddress->postal_code }}@endif<br>
                        {{ $billingAddress->country }}
                    </p>
                    @if($billingAddress->email)
                    <div class="mb-1"><i class="ti tabler-mail ti-xs text-muted me-1"></i><small>{{ $billingAddress->email }}</small></div>
                    @endif
                    @if($billingAddress->phone)
                    <div><i class="ti tabler-phone ti-xs text-muted me-1"></i><small>{{ $billingAddress->phone }}</small></div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Invoice --}}
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-file-invoice ti-md me-2 text-primary"></i> Invoice</h5>
                </div>
                <div class="card-body">
                    @if($order->invoice)
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <span class="fw-semibold small">#{{ $order->invoice->invoice_number }}</span>
                            <div class="text-muted" style="font-size:.7rem">{{ $order->invoice->issued_at->format('M d, Y') }}</div>
                        </div>
                        <span class="badge bg-label-{{ $order->invoice->status === 'paid' ? 'success' : 'warning' }} order-badge">{{ ucfirst($order->invoice->status) }}</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('invoices.show', $order->invoice->id) }}" target="_blank" class="btn btn-sm btn-label-primary">
                            <i class="ti tabler-printer ti-xs me-1"></i> Print
                        </a>
                        <a href="{{ route('invoices.download', $order->invoice->id) }}" class="btn btn-sm btn-label-success">
                            <i class="ti tabler-download ti-xs me-1"></i> Download PDF
                        </a>
                    </div>
                    @else
                    <div class="text-center py-2">
                        <i class="ti tabler-file-off ti-lg text-muted d-block mb-2"></i>
                        <p class="text-muted small mb-2">No invoice yet</p>
                        @if($order->payment_status === 'paid')
                        <form method="POST" action="{{ route('invoices.generate', $order->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary w-100"><i class="ti tabler-file-plus ti-xs me-1"></i> Generate</button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Seller Earnings --}}
            @if(isset($sellerSummary) && $sellerSummary->count())
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-coin ti-md me-2 text-primary"></i> Seller Earnings</h5>
                </div>
                <div class="card-body p-0">
                    @foreach($sellerSummary as $earning)
                    <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <a href="{{ route('sellers.show', $earning->seller_id) }}" class="fw-semibold small text-body">
                                {{ $earning->seller?->store_name ?? 'Seller #' . $earning->seller_id }}
                            </a>
                            <span class="badge bg-label-{{ match($earning->earning_status) { 'available' => 'success', 'pending' => 'warning', 'paid' => 'info', default => 'secondary' } }} order-badge">
                                {{ ucfirst($earning->earning_status) }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Gross</span>
                            <span>{{ format_currency($earning->total_gross) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Commission</span>
                            <span class="text-danger">-{{ format_currency($earning->total_commission) }}</span>
                        </div>
                        <div class="d-flex justify-content-between small fw-semibold border-top pt-1">
                            <span>Net</span>
                            <span class="text-success">{{ format_currency($earning->total_net) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Coupon Used --}}
            @if($couponMeta)
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-ticket ti-md me-2 text-primary"></i> Coupon</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary px-3 py-2" style="font-size:.8rem;letter-spacing:.5px">{{ $couponMeta['code'] }}</span>
                        <span class="fw-semibold text-danger">-{{ format_currency($couponMeta['discount'], $orderCurrency) }}</span>
                    </div>
                    <div class="text-muted small">
                        {{ $couponMeta['type'] === 'percent' ? $couponMeta['value'] . '% off' : format_currency($couponMeta['value']) . ' flat' }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Fraud / Risk Signals --}}
            <div class="card order-card sidebar-card">
                <div class="card-header">
                    <h5><i class="ti tabler-shield-check ti-md me-2 text-primary"></i> Risk Check</h5>
                </div>
                <div class="card-body">
                    @php
                        $riskSignals = [];
                        if ($order->user) {
                            $userOrderCount = $order->user->orders()->count();
                            if ($userOrderCount <= 1) $riskSignals[] = ['label' => 'First-time buyer', 'color' => 'warning', 'icon' => 'tabler-alert-triangle'];
                            $accountAge = $order->user->created_at->diffInDays(now());
                            if ($accountAge < 1) $riskSignals[] = ['label' => 'Account < 24h old', 'color' => 'danger', 'icon' => 'tabler-clock-exclamation'];
                        }
                        if ($walletMeta && ($walletMeta['full_wallet_pay'] ?? false)) {
                            $riskSignals[] = ['label' => 'Full wallet payment', 'color' => 'info', 'icon' => 'tabler-wallet'];
                        }
                        if (empty($riskSignals)) {
                            $riskSignals[] = ['label' => 'No risk signals detected', 'color' => 'success', 'icon' => 'tabler-shield-check'];
                        }
                    @endphp
                    @foreach($riskSignals as $signal)
                    <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-2' : '' }}">
                        <span class="badge bg-label-{{ $signal['color'] }} p-1"><i class="ti {{ $signal['icon'] }} ti-xs"></i></span>
                        <small class="{{ $signal['color'] === 'success' ? 'text-muted' : 'fw-medium' }}">{{ $signal['label'] }}</small>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Edit Billing Address Modal --}}
@if($billingAddress && !in_array($order->status, ['completed','refunded']))
<div class="modal fade" id="editBillingAddress" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('orders.billing.update', $order->id) }}">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti tabler-map-pin me-2"></i> Edit Billing Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $billingAddress->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $billingAddress->email) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required>{{ old('address', $billingAddress->address) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $billingAddress->city) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $billingAddress->state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $billingAddress->postal_code) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $billingAddress->phone) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" name="country" class="form-control" value="{{ old('country', $billingAddress->country) }}" required>
                        </div>
                        <input type="hidden" name="type" value="billing">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="ti tabler-check ti-xs me-1"></i> Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('page-js')
<script>
function deleteOrder(orderId) {
    Swal.fire({
        title: 'Delete this order?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: '{{ url("dashboard/orders") }}/' + orderId,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: () => {
                Swal.fire({ icon: 'success', title: 'Order deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true })
                    .then(() => window.location.href = '{{ route("orders.index") }}');
            },
            error: () => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not delete order.' })
        });
    });
}

function resendNotification(type) {
    Swal.fire({
        title: 'Resend notification?',
        text: 'This will send the email notification to the customer.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Send',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then(result => {
        if (!result.isConfirmed) return;
        $.post('{{ route("orders.resend-notification", $order->id) }}', {
            _token: '{{ csrf_token() }}',
            type: type
        }).done(data => {
            Swal.fire({ icon: 'success', title: 'Sent!', text: data.message || 'Notification sent successfully.', timer: 2000, showConfirmButton: false });
        }).fail(xhr => {
            const msg = xhr.responseJSON?.message || 'Failed to send notification.';
            Swal.fire({ icon: 'error', title: 'Error', text: msg });
        });
    });
}

function copyKey(el, text) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = el.className;
        el.className = el.className.replace('tabler-copy', 'tabler-check');
        el.style.color = '#28c76f';
        setTimeout(() => { el.className = orig; el.style.color = ''; }, 1200);
    });
}

$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
