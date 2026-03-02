@extends('layouts.app')
@section('title', 'Refund Request #' . $refund->id)

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-receipt-refund me-2"></i>Refund Request #{{ $refund->id }}</h4>
            <p class="text-muted mb-0">
                Submitted {{ $refund->created_at->diffForHumans() }} by {{ $refund->user?->name }}
            </p>
        </div>
        <a href="{{ route('refunds.index') }}" class="btn btn-label-secondary">
            <i class="ti tabler-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Refund Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Status</strong></div>
                        <div class="col-sm-9">
                            @php
                                $colors = ['pending' => 'warning', 'approved' => 'info', 'rejected' => 'danger', 'processing' => 'primary', 'completed' => 'success'];
                            @endphp
                            <span class="badge bg-label-{{ $colors[$refund->status] ?? 'secondary' }}">{{ ucfirst($refund->status) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Type</strong></div>
                        <div class="col-sm-9">{{ ucfirst($refund->type) }} Refund</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Amount</strong></div>
                        <div class="col-sm-9"><h5 class="text-danger mb-0">${{ number_format($refund->amount, 2) }}</h5></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Reason</strong></div>
                        <div class="col-sm-9">{{ $refund->reason }}</div>
                    </div>
                    @if($refund->description)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Description</strong></div>
                        <div class="col-sm-9">{{ $refund->description }}</div>
                    </div>
                    @endif
                    @if($refund->admin_note)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Admin Note</strong></div>
                        <div class="col-sm-9"><div class="alert alert-info mb-0">{{ $refund->admin_note }}</div></div>
                    </div>
                    @endif
                    @if($refund->processor)
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Processed By</strong></div>
                        <div class="col-sm-9">{{ $refund->processor->name }} on {{ $refund->processed_at?->format('M d, Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Order Info --}}
            @if($refund->order)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Order #{{ $refund->order->order_number }}</h5>
                    <a href="{{ route('orders.edit', $refund->order->id) }}" class="btn btn-sm btn-label-primary">
                        <i class="ti tabler-external-link me-1"></i> View Order
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Total Amount</small>
                            <h6>${{ number_format($refund->order->total_amount, 2) }}</h6>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Payment Status</small>
                            <h6>{{ ucfirst($refund->order->payment_status) }}</h6>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Order Status</small>
                            <h6>{{ ucfirst($refund->order->status) }}</h6>
                        </div>
                    </div>

                    @if($refund->order->items->count())
                    <hr>
                    <h6>Order Items</h6>
                    @foreach($refund->order->items as $item)
                    <div class="d-flex align-items-center gap-3 mb-2 p-2 border rounded">
                        @if($item->product?->image)
                        <img src="{{ asset($item->product->image) }}" alt="" class="rounded" width="40" height="40" style="object-fit:cover">
                        @endif
                        <div class="flex-grow-1">
                            <strong>{{ $item->product?->title ?? 'N/A' }}</strong>
                            <br><small class="text-muted">Qty: {{ $item->quantity }} &middot; ${{ number_format($item->subtotal, 2) }}</small>
                        </div>
                        @if($refund->order_item_id === $item->id)
                        <span class="badge bg-label-warning">Refund Item</span>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Actions --}}
            @if($refund->status === 'pending')
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Actions</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Note</label>
                        <textarea id="admin-note" class="form-control" rows="3" placeholder="Optional note..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success flex-grow-1" onclick="processRefund('approve')">
                            <i class="ti tabler-check me-1"></i> Approve
                        </button>
                        <button class="btn btn-danger flex-grow-1" onclick="processRefund('reject')">
                            <i class="ti tabler-x me-1"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @if($refund->status === 'approved')
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Process Refund</h5></div>
                <div class="card-body">
                    <p class="text-muted">Choose how to refund the customer:</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="issueRefund('wallet')">
                            <i class="ti tabler-wallet me-1"></i> Refund to Wallet
                        </button>
                        @if(!in_array($refund->order?->payment_method, ['cryptomus', 'crypto']))
                        <button class="btn btn-outline-primary" onclick="issueRefund('original')">
                            <i class="ti tabler-credit-card me-1"></i> Refund to Original
                        </button>
                        @else
                        <small class="text-muted mt-2">
                            <i class="ti tabler-info-circle me-1"></i> Original payment method (crypto) does not support automated refunds.
                        </small>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Customer --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Customer</h5></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <span>{{ substr($refund->user?->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $refund->user?->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $refund->user?->email }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seller --}}
            @if($refund->seller)
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Seller</h5></div>
                <div class="card-body">
                    <h6 class="mb-0">{{ $refund->seller->store_name }}</h6>
                    <small class="text-muted">{{ $refund->seller->slug }}</small>
                </div>
            </div>
            @endif
        </div>
    </div>

@endsection

@push('page-js')
<script>
function processRefund(action) {
    const note = document.getElementById('admin-note')?.value;
    const url = action === 'approve'
        ? '{{ route("refunds.approve", $refund->id) }}'
        : '{{ route("refunds.reject", $refund->id) }}';

    Swal.fire({
        title: action === 'approve' ? 'Approve Refund?' : 'Reject Refund?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post(url, { _token: '{{ csrf_token() }}', admin_note: note }, function (r) {
            Swal.fire('Done', r.message, 'success').then(() => location.reload());
        }).fail(e => Swal.fire('Error', e.responseJSON?.message || 'Failed', 'error'));
    });
}

function issueRefund(method) {
    Swal.fire({
        title: 'Process Refund?',
        text: method === 'wallet' ? 'Amount will be credited to customer wallet.' : 'Refund to original payment method.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Process',
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post('{{ route("refunds.process", $refund->id) }}', {
            _token: '{{ csrf_token() }}',
            method: method,
        }, function (r) {
            Swal.fire('Done', r.message, 'success').then(() => location.reload());
        }).fail(e => Swal.fire('Error', e.responseJSON?.message || 'Failed', 'error'));
    });
}
</script>
@endpush
