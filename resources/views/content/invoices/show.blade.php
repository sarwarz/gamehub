@extends('layouts.app')
@section('title', 'Invoice Preview')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-invoice.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endpush

@section('content')
<div class="row invoice-preview">

    <!-- Invoice -->
    <div class="col-xl-9 col-md-8 col-12 mb-md-0 mb-6">
        <div class="card invoice-preview-card p-sm-12 p-6">

            <!-- Header -->
            <div class="card-body invoice-preview-header rounded">
                <div class="d-flex justify-content-between flex-wrap gap-6">

                    <!-- Company + Meta -->
                    <div>
                        <h4 class="mb-2 text-heading fw-bold">
                            {{ config('app.name') }}
                        </h4>

                        <p class="mb-1">
                            Invoice #
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </p>

                        <p class="mb-1">
                            Order #
                            <span class="text-muted">{{ $invoice->order->order_number }}</span>
                        </p>

                        <p class="mb-0">
                            Status:
                            <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">
                                {{ strtoupper($invoice->status) }}
                            </span>
                        </p>
                    </div>

                    <!-- Amount + Dates -->
                    <div class="text-md-end">
                        <h4 class="mb-3 fw-bold text-primary">
                            {{ format_currency($invoice->grand_total) }}
                        </h4>

                        <p class="mb-1">
                            <span class="text-muted">Issued:</span>
                            <span class="fw-medium">
                                {{ $invoice->issued_at->format('d M Y') }}
                            </span>
                        </p>

                        @if($invoice->paid_at)
                        <p class="mb-0">
                            <span class="text-muted">Paid:</span>
                            <span class="fw-medium">
                                {{ $invoice->paid_at->format('d M Y') }}
                            </span>
                        </p>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Addresses -->
            <div class="card-body px-0">
                <div class="row">

                    <div class="col-md-6 mb-6">
                        <h6 class="mb-2">Invoice To</h6>
                        <p class="mb-1">{{ $invoice->user->name }}</p>
                        <p class="mb-0 text-muted">{{ $invoice->user->email }}</p>
                    </div>

                    <div class="col-md-6">
                        <h6 class="mb-2">Bill Summary</h6>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="pe-4">Subtotal</td>
                                    <td>{{ format_currency($invoice->subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td class="pe-4">Discount</td>
                                    <td>-{{ format_currency($invoice->discount_total) }}</td>
                                </tr>
                                <tr>
                                    <td class="pe-4">Tax</td>
                                    <td>{{ format_currency($invoice->tax_total) }}</td>
                                </tr>
                                <tr>
                                    <td class="pe-4 fw-medium">Total</td>
                                    <td class="fw-medium">
                                        {{ format_currency($invoice->grand_total) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Items -->
            <div class="table-responsive border border-bottom-0 border-top-0 rounded">
                <table class="table m-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="text-heading">
                                {{ $item->item_name }}
                            </td>
                            <td class="text-end">
                                {{ format_currency($item->unit_price) }}
                            </td>
                            <td class="text-end">
                                {{ $item->quantity }}
                            </td>
                            <td class="text-end fw-medium">
                                {{ format_currency($item->subtotal) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="table-responsive">
                <table class="table m-0 table-borderless">
                    <tbody>
                        <tr>
                            <td class="align-top pe-6 ps-0 py-6 text-muted">
                                Thank you for your business.
                            </td>

                            <td class="px-0 py-6 w-px-100">
                                <p class="mb-2">Subtotal</p>
                                <p class="mb-2">Discount</p>
                                <p class="mb-2 border-bottom pb-2">Tax</p>
                                <p class="mb-0 fw-medium">Total</p>
                            </td>

                            <td class="text-end px-0 py-6 w-px-120 fw-medium text-heading">
                                <p class="mb-2">{{ format_currency($invoice->subtotal) }}</p>
                                <p class="mb-2">-{{ format_currency($invoice->discount_total) }}</p>
                                <p class="mb-2 border-bottom pb-2">{{ format_currency($invoice->tax_total) }}</p>
                                <p class="mb-0 fw-bold">
                                    {{ format_currency($invoice->grand_total) }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Note -->
            @if(!empty($invoice->meta['note']))
            <hr class="mt-0 mb-6" />
            <div class="card-body p-0">
                <span class="fw-medium text-heading">Note:</span>
                <span class="text-muted">{{ $invoice->meta['note'] }}</span>
            </div>
            @endif

        </div>
    </div>
    <!-- /Invoice -->

    <!-- Actions -->
    <div class="col-xl-3 col-md-4 col-12 invoice-actions">
        <div class="card">
            <div class="card-body d-grid gap-3">

                <a target="_blank"
                   href="{{ route('invoices.print', $invoice) }}"
                   class="btn btn-label-secondary">
                    Print
                </a>

                <a href="{{ route('invoices.download', $invoice) }}"
                   class="btn btn-label-secondary">
                    Download PDF
                </a>

                @if($invoice->status !== 'paid')
                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                    @csrf
                    <button class="btn btn-success">
                        Mark as Paid
                    </button>
                </form>
                @endif

            </div>
        </div>
    </div>
    <!-- /Invoice Actions -->

</div>
@endsection
