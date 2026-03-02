@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->invoice_number)

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-invoice.css') }}">
<style>
    .invoice-badge { font-size: .68rem; letter-spacing: .3px; }
    .inv-header-band {
        background: linear-gradient(135deg, #696cff 0%, #8592ff 100%);
        color: #fff; border-radius: .5rem; padding: 1.5rem 2rem;
    }
    .inv-header-band a { color: rgba(255,255,255,.85); }
    .inv-header-band a:hover { color: #fff; }
    .inv-section-title {
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px;
        color: #696cff; margin-bottom: .5rem;
        border-bottom: 2px solid #696cff;
        padding-bottom: .25rem; display: inline-block;
    }
    .totals-row { display: flex; justify-content: space-between; padding: .35rem 0; }
    .totals-row.total-final { border-top: 2px solid #696cff; padding-top: .75rem; margin-top: .25rem; }
</style>
@endpush

@section('content')
<div class="row invoice-preview">

    {{-- Invoice Card --}}
    <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-4">
        <div class="card invoice-preview-card">

            {{-- Branded Header --}}
            <div class="card-body">
                <div class="inv-header-band mb-4">
                    <div class="d-flex justify-content-between flex-wrap gap-3">
                        <div>
                            <h4 class="mb-1 fw-bold text-white">{{ \App\Models\Setting::get('invoice', 'company_name', config('app.name')) }}</h4>
                            <p class="mb-0 small" style="opacity:.8">{{ \App\Models\Setting::get('invoice', 'company_address', '') }}</p>
                            @if($taxNumber = \App\Models\Setting::get('invoice', 'tax_number', ''))
                                <p class="mb-0 small" style="opacity:.75;font-size:.7rem">Tax No: {{ $taxNumber }}</p>
                            @endif
                        </div>
                        <div class="text-md-end">
                            <h3 class="mb-1 fw-bold text-white" style="letter-spacing:1px">INVOICE</h3>
                            <p class="mb-0 small" style="opacity:.9">
                                <strong>#{{ $invoice->invoice_number }}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Meta Row --}}
                <div class="row mb-4">
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti tabler-calendar ti-sm text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Issued</small>
                                <span class="fw-semibold">{{ $invoice->issued_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti tabler-credit-card ti-sm text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Paid</small>
                                @if($invoice->paid_at)
                                    <span class="fw-semibold text-success">{{ $invoice->paid_at->format('M d, Y') }}</span>
                                @else
                                    <span class="text-muted">Unpaid</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        @php
                            $sMap = ['draft' => 'secondary', 'issued' => 'info', 'paid' => 'success', 'cancelled' => 'danger'];
                        @endphp
                        <span class="badge bg-label-{{ $sMap[$invoice->status] ?? 'secondary' }} invoice-badge px-3 py-2">
                            {{ strtoupper($invoice->status) }}
                        </span>
                        <div class="mt-2">
                            <span class="fw-bold text-primary fs-4">{{ format_currency($invoice->grand_total) }}</span>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Addresses Row --}}
                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="inv-section-title">Invoice To</div>
                        @if($billing)
                            <p class="fw-semibold mb-1">{{ $billing->name }}</p>
                            <p class="text-muted small mb-0 lh-lg">
                                {{ $billing->address }}<br>
                                {{ $billing->city }}@if($billing->state), {{ $billing->state }}@endif
                                @if($billing->postal_code) {{ $billing->postal_code }}@endif<br>
                                {{ $billing->country }}
                                @if($billing->phone)<br><i class="ti tabler-phone ti-xs"></i> {{ $billing->phone }}@endif
                                <br><i class="ti tabler-mail ti-xs"></i> {{ $billing->email ?? $invoice->user->email }}
                            </p>
                        @else
                            <p class="fw-semibold mb-1">{{ $invoice->user->name }}</p>
                            <p class="text-muted small mb-0">{{ $invoice->user->email }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="inv-section-title">Order Details</div>
                        <table class="small">
                            <tr>
                                <td class="pe-3 text-muted py-1">Order</td>
                                <td class="fw-semibold py-1">
                                    <a href="{{ route('orders.edit', $invoice->order->id) }}" class="text-body">#{{ $invoice->order->order_number }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-3 text-muted py-1">Payment</td>
                                <td class="py-1">{{ ucfirst($invoice->order->payment_method ?? 'N/A') }}</td>
                            </tr>
                            <tr>
                                <td class="pe-3 text-muted py-1">Currency</td>
                                <td class="py-1">{{ strtoupper($invoice->currency ?? 'USD') }}</td>
                            </tr>
                            <tr>
                                <td class="pe-3 text-muted py-1">Order Status</td>
                                <td class="py-1">
                                    @php $oMap = ['pending'=>'warning','processing'=>'info','completed'=>'success','cancelled'=>'danger','refunded'=>'secondary']; @endphp
                                    <span class="badge bg-label-{{ $oMap[$invoice->order->status] ?? 'secondary' }}" style="font-size:.65rem">{{ ucfirst($invoice->order->status) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="inv-section-title">Items</div>
                <div class="table-responsive border rounded mb-4">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">#</th>
                                <th>Item</th>
                                <th class="text-end" width="130">Unit Price</th>
                                <th class="text-center" width="70">Qty</th>
                                <th class="text-end" width="140">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $idx => $item)
                            <tr>
                                <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $item->item_name }}</span>
                                    @if($item->orderItem?->product && $item->orderItem->product->title !== $item->item_name)
                                        <div class="text-muted small">{{ $item->orderItem->product->title }}</div>
                                    @endif
                                </td>
                                <td class="text-end">{{ format_currency($item->unit_price) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-semibold">{{ format_currency($item->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="row">
                    <div class="col-md-6">
                        @if(!empty($invoice->meta['note']))
                        <div class="inv-section-title">Note</div>
                        <p class="text-muted small fst-italic">{{ $invoice->meta['note'] }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div style="max-width:280px;margin-left:auto">
                            <div class="totals-row">
                                <span class="text-muted">Subtotal</span>
                                <span>{{ format_currency($invoice->subtotal) }}</span>
                            </div>
                            @if($invoice->discount_total > 0)
                            <div class="totals-row">
                                <span class="text-muted">Discount</span>
                                <span class="text-danger">-{{ format_currency($invoice->discount_total) }}</span>
                            </div>
                            @endif
                            @if($invoice->tax_total > 0)
                            <div class="totals-row">
                                <span class="text-muted">Tax</span>
                                <span>{{ format_currency($invoice->tax_total) }}</span>
                            </div>
                            @endif
                            <div class="totals-row total-final">
                                <span class="fw-bold fs-5">Total</span>
                                <span class="fw-bold fs-5 text-primary">{{ format_currency($invoice->grand_total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions Sidebar --}}
    <div class="col-xl-3 col-md-4 col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h6 class="card-title mb-0"><i class="ti tabler-settings ti-sm me-1 text-primary"></i> Actions</h6>
            </div>
            <div class="card-body d-grid gap-2 pt-4">
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-label-secondary">
                    <i class="ti tabler-printer ti-xs me-1"></i> Print Invoice
                </a>
                <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-label-success">
                    <i class="ti tabler-download ti-xs me-1"></i> Download PDF
                </a>
                @if($invoice->status !== 'paid')
                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                    @csrf
                    <button class="btn btn-success w-100">
                        <i class="ti tabler-check ti-xs me-1"></i> Mark as Paid
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Invoice Summary Card --}}
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h6 class="card-title mb-0"><i class="ti tabler-receipt ti-sm me-1 text-primary"></i> Summary</h6>
            </div>
            <div class="card-body pt-4">
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Invoice #</span>
                    <code>{{ $invoice->invoice_number }}</code>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Customer</span>
                    <span class="fw-semibold">{{ $invoice->user->name }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Items</span>
                    <span>{{ $invoice->items->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Status</span>
                    <span class="badge bg-label-{{ $sMap[$invoice->status] ?? 'secondary' }}" style="font-size:.65rem">{{ ucfirst($invoice->status) }}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-primary">{{ format_currency($invoice->grand_total) }}</span>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="card">
            <div class="card-body d-grid gap-2">
                <a href="{{ route('orders.edit', $invoice->order->id) }}" class="btn btn-label-info">
                    <i class="ti tabler-shopping-cart ti-xs me-1"></i> View Order
                </a>
                <a href="{{ route('invoices.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left ti-xs me-1"></i> Back to Invoices
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
