@extends('layouts.app')

@section('title', 'Order Details')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-order-edit">

@include('partials.alerts')

{{-- =========================
 ORDER HEADER
========================== --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1">
            Order #{{ $order->order_number }}

            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} ms-2">
                {{ ucfirst($order->payment_status) }}
            </span>

            <span class="badge bg-info ms-1">
                {{ ucfirst($order->status) }}
            </span>
        </h5>

        <small class="text-muted">
            Placed on {{ $order->created_at->format('M d, Y h:i A') }}
        </small>
    </div>

    <form method="POST" action="{{ route('orders.destroy', $order->id) }}"
          onsubmit="return confirm('Delete this order?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger"> Delete Order
        </button>
    </form>
</div>

<div class="row">

{{-- =========================
 LEFT COLUMN
========================== --}}
<div class="col-lg-8">

{{-- ORDER ITEMS --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Order Items</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead>
            <tr>
                <th>Product</th>
                <th class="text-end">Price</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        
                        <div class="d-flex align-items-start gap-3">
                            {{-- Product Image --}}
                            <img 
                                src="{{ $item->product->cover_image ?? asset('assets/img/placeholder/product.png') }}"
                                alt="{{ $item->product->title }}"
                                class="rounded border"
                                style="width:80px; height:80px; object-fit:cover;"
                            >

                            <div>
                                <strong>{{ $item->product->title }}</strong>
                                <br>

                                <small class="text-muted">
                                    Seller: {{ $item->seller->store_name ?? 'N/A' }}
                                </small>

                                {{-- Seller Earning --}}
                                @if($item->earning)
                                    <br>
                                    <small class="text-success d-block">
                                        Seller Net:
                                        {{ number_format($item->earning->net_amount, 2) }} {{ $order->currency }}
                                    </small>

                                    <small class="text-danger d-block">
                                        Platform Fee:
                                        {{ number_format($item->earning->commission, 2) }} {{ $order->currency }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>


                    <td class="text-end">
                        {{ number_format($item->unit_price, 2) }} {{ $order->currency }}
                    </td>

                    <td class="text-center">{{ $item->quantity }}</td>

                    <td class="text-end fw-semibold">
                        {{ number_format($item->subtotal, 2) }} {{ $order->currency }}
                    </td>
                </tr>

                {{-- DELIVERY DETAILS --}}
                @foreach($item->deliveries as $delivery)
                    <tr>
                        <td colspan="5">
                            <small>
                                <strong>Delivery:</strong>
                                {{ ucfirst($delivery->delivery_method) }}
                                |
                                Status: {{ ucfirst($delivery->status) }}
                                @if($delivery->delivered_at)
                                    |
                                    Delivered at: {{ $delivery->delivered_at->format('M d, Y h:i A') }}
                                @endif
                            </small>
                        </td>
                    </tr>
                @endforeach

            @endforeach
            </tbody>
        </table>
    </div>

    {{-- TOTALS --}}
    <div class="p-3 d-flex justify-content-end">
        <div style="width:300px">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <strong>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</strong>
            </div>
            <div class="d-flex justify-content-between border-top pt-2">
                <span>Total</span>
                <strong class="text-primary">
                    {{ number_format($order->total_amount, 2) }} {{ $order->currency }}
                </strong>
            </div>
        </div>
    </div>
</div>





{{-- =========================
   ORDER HISTORY (TIMELINE)
========================== --}}
<div class="card mb-6 mt-4">
    <div class="card-header">
        <h5 class="card-title m-0">Order History</h5>
    </div>

    <div class="card-body pt-1">
        <ul class="timeline pb-0 mb-0">

            {{-- Order Created --}}
            <li class="timeline-item timeline-item-transparent border-primary">
                <span class="timeline-point timeline-point-primary"></span>
                <div class="timeline-event">
                    <div class="timeline-header">
                        <h6 class="mb-0">
                            Order placed ({{ $order->order_number }})
                        </h6>
                        <small class="text-body-secondary">
                            {{ $order->created_at->format('M d, Y h:i A') }}
                        </small>
                    </div>
                    <p class="mt-3">
                        Order was created successfully.
                    </p>
                </div>
            </li>

            {{-- Payment Transactions --}}
            @foreach($order->transactions as $txn)
                <li class="timeline-item timeline-item-transparent border-primary">
                    <span class="timeline-point timeline-point-primary"></span>
                    <div class="timeline-event">
                        <div class="timeline-header">
                            <h6 class="mb-0">
                                Payment {{ ucfirst($txn->status) }}
                            </h6>
                            <small class="text-body-secondary">
                                {{ $txn->created_at->format('M d, Y h:i A') }}
                            </small>
                        </div>
                        <p class="mt-3 mb-0">
                            {{ ucfirst($txn->type) }} • {{ ucfirst($txn->category) }} —
                            {{ number_format($txn->amount, 2) }} {{ $txn->currency }}
                            <br>
                            <small class="text-muted">TRX: {{ $txn->trx }}</small>
                        </p>
                    </div>
                </li>
            @endforeach

            {{-- Invoice --}}
            @if($order->invoice)
                <li class="timeline-item timeline-item-transparent border-primary">
                    <span class="timeline-point timeline-point-primary"></span>
                    <div class="timeline-event">
                        <div class="timeline-header">
                            <h6 class="mb-0">
                                Invoice generated
                            </h6>
                            <small class="text-body-secondary">
                                {{ $order->invoice->issued_at->format('M d, Y') }}
                            </small>
                        </div>
                        <p class="mt-3 mb-0">
                            Invoice #{{ $order->invoice->invoice_number }} was generated.
                        </p>
                    </div>
                </li>
            @endif

            {{-- Deliveries --}}
            @foreach($order->items as $item)
                @foreach($item->deliveries as $delivery)
                    <li class="timeline-item timeline-item-transparent border-primary">
                        <span class="timeline-point timeline-point-primary"></span>
                        <div class="timeline-event">
                            <div class="timeline-header">
                                <h6 class="mb-0">
                                    Delivery {{ ucfirst($delivery->status) }}
                                </h6>
                                <small class="text-body-secondary">
                                    {{ optional($delivery->delivered_at)->format('M d, Y h:i A') ?? 'Pending' }}
                                </small>
                            </div>
                            <p class="mt-3 mb-0">
                                {{ ucfirst($delivery->delivery_method) }} delivery
                                @if($delivery->status === 'delivered')
                                    completed successfully.
                                @else
                                    is in progress.
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            @endforeach

            {{-- Final Order Status --}}
            <li class="timeline-item timeline-item-transparent border-dashed pb-0">
                <span class="timeline-point timeline-point-{{ 
                    $order->status === 'completed' ? 'success' :
                    ($order->status === 'cancelled' ? 'danger' :
                    ($order->status === 'refunded' ? 'warning' : 'secondary'))
                }}"></span>

                <div class="timeline-event pb-0">
                    <div class="timeline-header">
                        <h6 class="mb-0">
                            Order {{ ucfirst($order->status) }}
                        </h6>
                        <small class="text-body-secondary">
                            {{ now()->format('M d, Y') }}
                        </small>
                    </div>
                    <p class="mt-1 mb-0">
                        Current order status is <strong>{{ ucfirst($order->status) }}</strong>.
                    </p>
                </div>
            </li>

        </ul>
    </div>
</div>




</div>

{{-- =========================
 RIGHT COLUMN
========================== --}}
<div class="col-lg-4">

{{-- UPDATE STATUS --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Update Order Status</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('orders.update', $order->id) }}">
            @csrf
            @method('PUT')

            <select name="status" class="form-select mb-3">
                @foreach(['pending','processing','completed','refunded','cancelled'] as $status)
                    <option value="{{ $status }}" @selected($order->status === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-primary w-100">Save</button>
        </form>
    </div>
</div>

{{-- =========================
   CUSTOMER DETAILS
========================== --}}
<div class="card mb-6">
    <div class="card-header">
        <h5 class="card-title m-0">Customer details</h5>
    </div>

    <div class="card-body">

        {{-- Avatar + Name --}}
        <div class="d-flex justify-content-start align-items-center mb-6">
            <div class="avatar me-3">
                <img
                    src="{{ $order->user?->avatar_url ?? asset('assets/img/avatars/1.png') }}"
                    alt="Avatar"
                    class="rounded-circle"
                >
            </div>

            <div class="d-flex flex-column">
                @if($order->user)
                    <a href="{{ route('users.show', $order->user->id) }}"
                       class="text-body text-nowrap">
                        <h6 class="mb-0">{{ $order->user->name }}</h6>
                    </a>
                    <span>Customer ID: #{{ $order->user->id }}</span>
                @else
                    <h6 class="mb-0">{{ $order->guest_name }}</h6>
                    <span class="badge bg-secondary mt-1">Guest Customer</span>
                @endif
            </div>
        </div>

        {{-- Orders Count --}}
        <div class="d-flex justify-content-start align-items-center mb-6">
            <span
                class="avatar rounded-circle bg-label-success me-3 d-flex align-items-center justify-content-center">
                <i class="ti tabler-shopping-cart icon-lg"></i>
            </span>

            <h6 class="text-nowrap mb-0">
                {{ $order->user?->orders()->count() ?? 1 }}
                {{ Str::plural('Order', $order->user?->orders()->count() ?? 1) }}
            </h6>
        </div>

        {{-- Contact Info Header --}}
        <div class="d-flex justify-content-between">
            <h6 class="mb-1">Contact info</h6>

            @if($order->user)
                <h6 class="mb-1">
                    <a href="{{ route('users.edit', $order->user->id) }}">Edit</a>
                </h6>
            @endif
        </div>

        {{-- Contact Info --}}
        <p class="mb-1">
            Email:
            {{ $order->user?->email ?? $order->guest_email }}
        </p>

        <p class="mb-0">
            Mobile:
            {{ $order->user?->phone ?? 'N/A' }}
        </p>

    </div>
</div>


{{-- ADDRESSES --}}
@foreach($order->addresses as $address)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ ucfirst($address->type) }} Address</h5>
    </div>
    <div class="card-body">
        <p class="mb-1">{{ $address->name }}</p>
        <p class="mb-1">{{ $address->address }}</p>
        <p class="mb-1">{{ $address->city }}, {{ $address->country }}</p>
        <p class="mb-0">{{ $address->email }}</p>
    </div>
</div>
@endforeach

{{-- =========================
   INVOICE ACTIONS
========================== --}}

@if($order->invoice)
    <div class="card mt-4 mt-4">
        <div class="card-header">
            <h5 class="card-title m-0">Invoice</h5>
        </div>
        <div class="card-body d-grid gap-2">

            {{-- View / Print Invoice --}}
            <a href="{{ route('invoices.show', $order->invoice->id) }}"
               target="_blank"
               class="btn btn-outline-primary">
                <i class="ti tabler-printer"></i>
                Print Invoice
            </a>

            {{-- Download Invoice --}}
            <a href="{{ route('invoices.download', $order->invoice->id) }}"
               class="btn btn-outline-success">
                <i class="ti tabler-download"></i>
                Download Invoice (PDF)
            </a>

        </div>
    </div>
@else
    <div class="alert alert-warning mt-4 mb-0">
        Invoice not generated yet.
    </div>
@endif

{{-- PAYMENT --}}
<div class="card mb-4 mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Payment</h5>
    </div>
    <div class="card-body">
        <p class="mb-1"><strong>Method:</strong> {{ $order->payment_method ?? 'N/A' }}</p>
        <p class="mb-0">
            <strong>Status:</strong>
            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                {{ ucfirst($order->payment_status) }}
            </span>
        </p>
    </div>
</div>

{{-- TRANSACTIONS --}}
@if($order->transactions->count())
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Transactions</h5>
    </div>
    <ul class="list-group list-group-flush">
        @foreach($order->transactions as $txn)
            <li class="list-group-item">
                <strong>{{ strtoupper($txn->type) }}</strong>
                ({{ ucfirst($txn->category) }})
                — {{ number_format($txn->amount, 2) }} {{ $txn->currency }}
                <br>
                <small class="text-muted">
                    TRX: {{ $txn->trx }} • {{ $txn->created_at->format('M d, Y h:i A') }}
                </small>
            </li>
        @endforeach
    </ul>
</div>
@endif



</div>
</div>
</div>
@endsection
