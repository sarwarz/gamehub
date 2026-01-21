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
            Payment via {{ $order->payment_method ?? 'N/A' }}.
            Placed on {{ $order->created_at->format('M d, Y h:i A') }}.
            Customer IP: {{ $order->meta['client']['ip'] ?? '—' }}.
            User Agent: {{ Str::limit($order->meta['client']['user_agent'] ?? '—', 60) }}.
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
      <div class="col-lg-9">
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
                                 {{ format_currency($item->earning->net_amount) }} 
                                 </small>
                                 <small class="text-danger d-block">
                                 Platform Fee:
                                 {{ format_currency($item->earning->commission) }}
                                 </small>
                                 @endif
                              </div>
                           </div>
                        </td>
                        <td class="text-end">
                           {{ format_currency($item->unit_price) }}
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end fw-semibold">
                           {{ format_currency($item->subtotal) }}
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
                     <strong>{{ format_currency($order->subtotal) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between border-top pt-2">
                     <span>Total</span>
                     <strong class="text-primary">
                     {{ format_currency($order->total_amount) }}
                     </strong>
                  </div>
               </div>
            </div>
         </div>
         {{-- =========================
         ORDER Delivery 
         ========================== --}}
         <div class="card mb-6 mt-4">
            <div class="card-header">
               <h5 class="card-title m-0">Order Delivery</h5>
            </div>
            <div class="card-body pt-1">
               <div class="table-responsive mt-4">
                  <table class="table table-bordered align-middle">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th>Product</th>
                           <th>Delivery Type</th>
                           <th>Status</th>
                           <th>Delivered Items</th>
                           <th>Delivered At</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($order->items as $item)
                        @foreach($item->deliveries as $delivery)
                        <tr>
                           <td>{{ $loop->iteration }}</td>
                           <td>
                              <strong>{{ $item->product->title }}</strong><br>
                              <small class="text-muted">
                              Qty: {{ $item->quantity }}
                              </small>
                           </td>
                           <td>
                              <span class="badge bg-info">
                              {{ ucfirst($delivery->delivery_method) }}
                              </span>
                           </td>
                           <td>
                              <span class="badge bg-{{ 
                                 $delivery->status === 'delivered' ? 'success' :
                                 ($delivery->status === 'failed' ? 'danger' : 'warning')
                                 }}">
                              {{ ucfirst($delivery->status) }}
                              </span>
                           </td>
                           <td>
                              {{-- Delivered content --}}
                              @if($delivery->status === 'delivered')
                              {{-- License Keys --}}
                              @if(($delivery->payload['type'] ?? null) === 'license')
                              <ul class="list-unstyled mb-0">
                                 @foreach($delivery->payload['keys'] as $key)
                                 <li>
                                    <code>{{ $key }}</code>
                                 </li>
                                 @endforeach
                              </ul>
                              @endif
                              {{-- Download Link --}}
                              @if(isset($delivery->payload['download_link']))
                              <a href="{{ $delivery->payload['download_link'] }}"
                                 target="_blank"
                                 class="btn btn-sm btn-outline-primary">
                              Download
                              </a>
                              @endif
                              @elseif($delivery->status === 'failed')
                              <span class="text-danger">
                              {{ $delivery->payload['error'] ?? 'Delivery failed' }}
                              </span>
                              @else
                              <span class="text-muted">
                              Pending
                              </span>
                              @endif
                           </td>
                           <td>
                              {{ optional($delivery->delivered_at)->format('M d, Y h:i A') ?? '-' }}
                           </td>
                           <td>
                              {{-- Retry --}}
                              @if($delivery->status === 'failed')
                              <form method="POST"
                                 action="{{ route('admin.deliveries.retry', $delivery->id) }}">
                                 @csrf
                                 <button class="btn btn-warning btn-sm">
                                 Retry
                                 </button>
                              </form>
                              @else
                              —
                              @endif
                           </td>
                        </tr>
                        @endforeach
                        @endforeach
                     </tbody>
                  </table>
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
                           {{ format_currency($txn->amount) }} 
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

         {{-- TRANSACTIONS --}}
         @if($order->transactions->count())
         <div class="card mb-4 mt-4">
            <div class="card-header">
               <h5 class="card-title mb-0">Transactions</h5>
            </div>
            <ul class="list-group list-group-flush">
               @foreach($order->transactions as $txn)
               <li class="list-group-item">
                  <strong>{{ strtoupper($txn->type) }}</strong>
                  ({{ ucfirst($txn->category) }})
                  — {{ format_currency($txn->amount) }}
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

      {{-- =========================
      RIGHT COLUMN
      ========================== --}}
      <div class="col-lg-3">
         {{-- UPDATE STATUS --}}
         <div class="card mb-6">
            <div class="card-header">
               <h5 class="card-title mb-0">Order Status</h5>
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
                  <button class="btn btn-primary w-100">Update</button>
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
            </div>
         </div>

         {{-- ADDRESSES --}}
         @if($billingAddress)
         <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
               <h5 class="card-title m-0">Billing Address</h5>
               <a href="javascript:void(0)"
                  data-bs-toggle="modal"
                  data-bs-target="#editBillingAddress">
               Edit
               </a>
            </div>
            <div class="card-body">
               <p class="mb-4">
                  {{ $billingAddress->name }} <br>
                  {{ $billingAddress->address }} <br>
                  {{ $billingAddress->city }}
                  @if($billingAddress->postal_code)
                  , {{ $billingAddress->postal_code }}
                  @endif
                  <br>
                  {{ $billingAddress->country }}
               </p>
               <p class="mb-2">
                  <strong>Email Address:</strong><br>
                  {{ $billingAddress->email }}
               </p>
               <p class="mb-0">
                  <strong>Phone:</strong><br>
                  {{ $billingAddress->phone ?? 'N/A' }}
               </p>
            </div>
         </div>
         @endif

         {{-- =========================
         ORDER NOTES
         ========================== --}}
         <div class="card mb-6">
            <div class="card-header">
               <h5 class="card-title mb-0">Order notes</h5>
            </div>
            <div class="card-body">
               {{-- Notes list --}}
               <div class="mb-3" style="max-height: 400px; overflow-y: auto">
                  @forelse($order->notes as $note)
                  <div class="p-2 mb-2 rounded
                     {{ $note->type === 'system' ? 'bg-light' : 'bg-body-secondary' }}">
                     <p class="mb-1 small">
                        {{ $note->note }}
                     </p>
                     <small class="text-muted">
                     {{ $note->created_at->format('F d, Y \a\t h:i a') }}
                     @if($note->user)
                     by {{ $note->user->name }}
                     @else
                     by System
                     @endif
                     </small>
                     @if(auth()->user()->can('delete', $note))
                     <div>
                        <form method="POST"
                           action="{{ route('orders.notes.destroy', $note->id) }}">
                           @csrf
                           @method('DELETE')
                           <button class="btn btn-link p-0 text-danger small">
                           Delete note
                           </button>
                        </form>
                     </div>
                     @endif
                  </div>
                  @empty
                  <p class="text-muted small mb-0">No notes yet.</p>
                  @endforelse
               </div>
               {{-- Add note --}}
               <form method="POST" action="{{ route('orders.notes.store', $order->id) }}">
                  @csrf
                  <div class="mb-2">
                     <textarea name="note"
                        class="form-control"
                        rows="3"
                        placeholder="Add a note..."></textarea>
                  </div>
                  <div class="d-flex gap-2">
                     <select name="visibility" class="form-select">
                        <option value="private">Private note</option>
                        <option value="customer">Note to customer</option>
                     </select>
                     <button class="btn btn-primary">
                     Add
                     </button>
                  </div>
               </form>
            </div>
         </div>

         {{-- =========================
         INVOICE ACTIONS
         ========================== --}}
         @if($order->invoice)
         <div class="card mb-6">
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
         <div class="card mt-4 mb-4">
            <div class="card-body">
               <div class="alert alert-warning mb-3">
                  Invoice not generated yet.
               </div>
               <form method="POST"
                  action="{{ route('invoices.generate', $order->id) }}"
                  onsubmit="return confirm('Generate invoice for this order?')">
                  @csrf
                  <button type="submit" class="btn btn-primary w-100">
                  <i class="ti tabler-file-plus"></i>
                  Generate Invoice
                  </button>
               </form>
            </div>
         </div>
         @endif
      </div>
   </div>
</div>

@if($billingAddress && !in_array($order->status, ['completed','refunded']))
<div class="modal fade" id="editBillingAddress" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <form method="POST" action="{{ route('orders.billing.update', $order->id) }}">
         @csrf
         @method('PUT')
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Billing Address</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
               <div class="row g-3">
                  {{-- Full Name --}}
                  <div class="col-md-6">
                     <label class="form-label">Full Name</label>
                     <input type="text"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $billingAddress->name) }}"
                        required>
                  </div>
                  {{-- Email --}}
                  <div class="col-md-6">
                     <label class="form-label">Email</label>
                     <input type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $billingAddress->email) }}"
                        required>
                  </div>
                  {{-- Address --}}
                  <div class="col-12">
                     <label class="form-label">Address</label>
                     <textarea name="address"
                        class="form-control @error('address') is-invalid @enderror"
                        rows="2"
                        required>{{ old('address', $billingAddress->address) }}</textarea>
                  </div>
                  {{-- City --}}
                  <div class="col-md-6">
                     <label class="form-label">City</label>
                     <input type="text"
                        name="city"
                        class="form-control @error('city') is-invalid @enderror"
                        value="{{ old('city', $billingAddress->city) }}"
                        required>
                  </div>
                  {{-- State --}}
                  <div class="col-md-6">
                     <label class="form-label">State</label>
                     <input type="text"
                        name="state"
                        class="form-control"
                        value="{{ old('state', $billingAddress->state) }}">
                  </div>
                  {{-- ZIP --}}
                  <div class="col-md-6">
                     <label class="form-label">ZIP Code</label>
                     <input type="text"
                        name="postal_code"
                        class="form-control"
                        value="{{ old('postal_code', $billingAddress->postal_code) }}">
                  </div>
                  {{-- Phone --}}
                  <div class="col-md-6">
                     <label class="form-label">Phone</label>
                     <input type="text"
                        name="phone"
                        class="form-control"
                        value="{{ old('phone', $billingAddress->phone) }}">  
                  </div>
                  {{-- Country --}}
                  <div class="col-md-12">
                     <label class="form-label">Country</label>
                     <input type="text"
                        name="country"
                        class="form-control @error('country') is-invalid @enderror"
                        value="{{ old('country', $billingAddress->country) }}"
                        required>
                  </div>
                  <input type="hidden" name="type" value="billing">
               </div>
            </div>
            <div class="modal-footer">
               <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-dismiss="modal">
               Cancel
               </button>
               <button type="submit" class="btn btn-primary">
               Save Changes
               </button>
            </div>
         </div>
      </form>
   </div>
</div>
@endif
@endsection