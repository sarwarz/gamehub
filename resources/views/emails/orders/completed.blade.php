@extends('emails.orders.layout')

@section('title', 'Order Complete')

@section('banner')
<div class="status-icon" style="background:#dcfce7; color:#16a34a">&#127881;</div>
<h2 class="status-title">Order Complete!</h2>
<p class="status-sub">All items have been delivered</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Your order <strong>#{{ $order->order_number }}</strong> is complete. All items have been successfully delivered!</p>

@include('emails.orders._order-meta')

{{-- Delivery Keys Section --}}
@include('emails.orders._delivery-keys')

@include('emails.orders._items-table', ['showDeliveryStatus' => true])

<hr class="divider">

@if($order->invoice)
<div class="info-box" style="background:#f0fdf4; border:1px solid #bbf7d0">
    <strong style="color:#166534">&#128206; Invoice Attached</strong><br>
    Your invoice <strong>{{ $order->invoice->invoice_number }}</strong> is attached to this email as a PDF for your records.
</div>
@endif

<div class="info-box">
    <strong>Need help?</strong> If you encounter any issues activating your products, don't hesitate to open a support ticket. We're here to help!
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View My Order</a>
</div>
@endsection
