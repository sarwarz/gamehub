@extends('layouts.app')
@section('title', 'Edit Order')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-order-edit">

    @include('partials.alerts')

    <!-- Order Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
        <div class="d-flex flex-column justify-content-center">
            <div class="mb-1">
                <span class="h5">Order #{{ $order->reference ?? $order->id }} </span>
                @if($order->paid_at)
                    <span class="badge bg-success ms-2">Paid</span>
                @else
                    <span class="badge bg-warning ms-2">Unpaid</span>
                @endif
                <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
            </div>
            <p class="mb-0">
                {{ $order->created_at->format('M d, Y, h:i A') }}
            </p>
        </div>
        <div class="d-flex align-content-center flex-wrap gap-2">
            <form action="{{ route('orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Delete this order?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-label-danger">
                    <i class="ti ti-trash"></i> Delete Order
                </button>
            </form>
        </div>
    </div>

    <!-- Order Details -->
    <div class="row">
        <div class="col-12 col-lg-8">

            <!-- Products -->
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Products</h5></div>
                <div class="card-datatable">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->products as $product)
                                <tr>
                                    <td>
                                        {{ $product->title }}
                                        @if($product->keys->count())
                                            <br>
                                            <small class="text-muted">Keys:
                                                @foreach($product->keys as $key)
                                                    <span class="badge bg-secondary">{{ $key->value }}</span>
                                                @endforeach
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $order->currency_symbol }} {{ number_format($product->pivot->unit_price, 2) }}</td>
                                    <td class="text-center">{{ $product->pivot->quantity }}</td>
                                    <td class="text-end">{{ $order->currency_symbol }} {{ number_format($product->pivot->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="d-flex justify-content-end align-items-center p-4">
                        <div class="order-calculations">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-heading">Subtotal:</span>
                                <h6 class="mb-0">{{ $order->currency_symbol }} {{ number_format($order->subtotal, 2) }}</h6>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-heading">Fees:</span>
                                <h6 class="mb-0">{{ $order->currency_symbol }} {{ number_format($order->fees, 2) }}</h6>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-heading">Commission:</span>
                                <h6 class="mb-0">{{ $order->currency_symbol }} {{ number_format($order->commission, 2) }}</h6>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Total:</h6>
                                <h6 class="mb-0 text-primary">{{ $order->currency_symbol }} {{ number_format($order->total, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            @if($order->transactions->count())
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Transactions</h5></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($order->transactions as $txn)
                            <li class="list-group-item">
                                <strong>{{ ucfirst($txn->type) }}</strong> via {{ $txn->gateway }} — 
                                {{ $order->currency_symbol }} {{ number_format($txn->amount,2) }}
                                <br><small>ID: {{ $txn->transaction_id ?? 'N/A' }}</small>
                                <br><small>{{ $txn->created_at->format('M d, Y h:i A') }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- Notes -->
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Order Notes</h5></div>
                <div class="card-body">
                    <ul class="list-group mb-3">
                        @foreach($order->notes as $note)
                            <li class="list-group-item">
                                {{ $note->note }}
                                <br><small>By {{ $note->user->name ?? 'System' }} — {{ $note->created_at->diffForHumans() }}</small>
                                @if($note->is_private)
                                    <span class="badge bg-secondary ms-2">Private</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <form action="{{ route('orders.notes.store',$order->id) }}" method="POST">
                        @csrf
                        <textarea name="note" class="form-control mb-2" placeholder="Add note"></textarea>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="private" name="is_private" value="1" checked>
                            <label for="private" class="form-check-label">Private</label>
                        </div>
                        <button class="btn btn-sm btn-primary">Add Note</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">

            <!-- Update Order -->
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Update Order</h5></div>
                <div class="card-body">
                    <form action="{{ route('orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Order Status</label>
                            <select name="status" class="form-select" required>
                                @foreach(['pending','processing','delivered','completed','refunded','cancelled'] as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Customer -->
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Customer Details</h5></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $order->buyer->name }}</h6>
                            <small>ID: #{{ $order->buyer->id }}</small>
                        </div>
                    </div>
                    <p class="mb-1">Email: {{ $order->buyer->email }}</p>
                </div>
            </div>

            <!-- Order Addresses -->
            @if($order->addresses->count())
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Addresses</h5></div>
                <div class="card-body">
                    @foreach($order->addresses as $addr)
                        <div class="mb-3 p-3 border rounded bg-light">
                            <h6 class="mb-2"><span class="badge bg-label-primary">{{ ucfirst($addr->type) }}</span></h6>
                            <p class="mb-1"><strong>{{ $addr->full_name }}</strong></p>
                            <p class="mb-1">{{ $addr->address_line1 }} {{ $addr->address_line2 }}</p>
                            <p class="mb-1">{{ $addr->city }}, {{ $addr->state }} {{ $addr->postal_code }}</p>
                            <p class="mb-1">{{ $addr->country }}</p>
                            @if($addr->phone)<p class="mb-1"><i class="ti ti-phone"></i> {{ $addr->phone }}</p>@endif
                            @if($addr->email)<p class="mb-0"><i class="ti ti-mail"></i> {{ $addr->email }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Payment -->
            <div class="card mb-6">
                <div class="card-header"><h5 class="card-title m-0">Payment</h5></div>
                <div class="card-body">
                    <p class="mb-1"><strong>Method:</strong> {{ $order->payment_method ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Reference:</strong> {{ $order->payment_ref ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Status:</strong>
                        @if($order->paid_at)
                            <span class="badge bg-success">Paid</span>
                        @else
                            <span class="badge bg-warning">Unpaid</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
