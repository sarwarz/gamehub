@extends('layouts.app')
@section('title', 'Preview Invoice')

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
            <div class="d-flex justify-content-between flex-xl-row flex-md-column flex-sm-row flex-column align-items-xl-center align-items-md-start align-items-sm-center align-items-start">

               <!-- Company -->
               <div class="mb-xl-0 mb-6 text-heading">
                  <div class="d-flex svg-illustration mb-6 gap-2 align-items-center">
                     <span class="app-brand-text fw-bold fs-4 ms-50">
                        {{ config('app.name') }}
                     </span>
                  </div>

                  <p class="mb-2">Invoice #: <strong>{{ $invoice->invoice_number }}</strong></p>
                  <p class="mb-2">Order #: {{ $invoice->order->order_number }}</p>
                  <p class="mb-0">
                     Status:
                     <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'primary' }}">
                        {{ ucfirst($invoice->status) }}
                     </span>
                  </p>
               </div>

               <!-- Dates -->
               <div>
                  <h5 class="mb-6">
                     {{ strtoupper($invoice->currency) }}
                     {{ number_format($invoice->grand_total, 2) }}
                  </h5>

                  <div class="mb-1 text-heading">
                     <span>Date Issued:</span>
                     <span class="fw-medium">
                        {{ $invoice->issued_at->format('d M Y') }}
                     </span>
                  </div>

                  @if($invoice->paid_at)
                  <div class="text-heading">
                     <span>Date Paid:</span>
                     <span class="fw-medium">
                        {{ $invoice->paid_at->format('d M Y') }}
                     </span>
                  </div>
                  @endif
               </div>
            </div>
         </div>

         <!-- Addresses -->
         <div class="card-body px-0">
            <div class="row">

               <div class="col-xl-6 col-md-12 col-sm-5 col-12 mb-xl-0 mb-md-6 mb-sm-0 mb-6">
                  <h6>Invoice To:</h6>
                  <p class="mb-1">{{ $invoice->user->name }}</p>
                  <p class="mb-1">{{ $invoice->user->email }}</p>
               </div>

               <div class="col-xl-6 col-md-12 col-sm-7 col-12">
                  <h6>Bill Summary:</h6>
                  <table>
                     <tbody>
                        <tr>
                           <td class="pe-4">Subtotal:</td>
                           <td>{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                           <td class="pe-4">Discount:</td>
                           <td>-{{ number_format($invoice->discount_total, 2) }}</td>
                        </tr>
                        <tr>
                           <td class="pe-4">Tax:</td>
                           <td>{{ number_format($invoice->tax_total, 2) }}</td>
                        </tr>
                        <tr>
                           <td class="pe-4 fw-medium">Total:</td>
                           <td class="fw-medium">
                              {{ number_format($invoice->grand_total, 2) }}
                              {{ strtoupper($invoice->currency) }}
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>

            </div>
         </div>

         <!-- Items Table -->
         <div class="table-responsive border border-bottom-0 border-top-0 rounded">
            <table class="table m-0">
               <thead>
                  <tr>
                     <th>Item</th>
                     <th>Cost</th>
                     <th>Qty</th>
                     <th>Price</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach($invoice->items as $item)
                  <tr>
                     <td class="text-nowrap text-heading">
                        {{ $item->item_name }}
                     </td>
                     <td>
                        {{ number_format($item->unit_price, 2) }}
                     </td>
                     <td>{{ $item->quantity }}</td>
                     <td>
                        {{ number_format($item->subtotal, 2) }}
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
                     <td class="align-top pe-6 ps-0 py-6 text-body">
                        <span>Thank you for your business.</span>
                     </td>

                     <td class="px-0 py-6 w-px-100">
                        <p class="mb-2">Subtotal:</p>
                        <p class="mb-2">Discount:</p>
                        <p class="mb-2 border-bottom pb-2">Tax:</p>
                        <p class="mb-0">Total:</p>
                     </td>

                     <td class="text-end px-0 py-6 w-px-100 fw-medium text-heading">
                        <p class="fw-medium mb-2">{{ number_format($invoice->subtotal, 2) }}</p>
                        <p class="fw-medium mb-2">-{{ number_format($invoice->discount_total, 2) }}</p>
                        <p class="fw-medium mb-2 border-bottom pb-2">{{ number_format($invoice->tax_total, 2) }}</p>
                        <p class="fw-medium mb-0">
                           {{ number_format($invoice->grand_total, 2) }}
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
            <span>{{ $invoice->meta['note'] }}</span>
         </div>
         @endif

      </div>
   </div>
   <!-- /Invoice -->

   <!-- Actions -->
   <div class="col-xl-3 col-md-4 col-12 invoice-actions">
      <div class="card">
         <div class="card-body">

            <a target="_blank"
               href="{{ route('invoices.print', $invoice) }}"
               class="btn btn-label-secondary d-grid w-100 mb-4">
               Print
            </a>

            <a href="{{ route('invoices.download', $invoice) }}"
               class="btn btn-label-secondary d-grid w-100 mb-4">
               Download
            </a>

            @if($invoice->status !== 'paid')
            <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
               @csrf
               <button class="btn btn-success d-grid w-100">
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
