@extends('emails.orders.layout')

@section('title', 'Payment Confirmed')

@section('banner')
<div class="status-icon" style="background:#dcfce7; color:#16a34a">&#128179;</div>
<h2 class="status-title">Payment Confirmed</h2>
<p class="status-sub">We've received your payment</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Great news! Your payment for order <strong>#{{ $order->order_number }}</strong> has been confirmed.</p>

@include('emails.orders._order-meta')

@include('emails.orders._items-table', ['showDeliveryStatus' => false])

<hr class="divider">

@if($order->invoice)
<div class="info-box" style="background:#f0fdf4; border:1px solid #bbf7d0">
    <strong style="color:#166534">&#128206; Invoice Attached</strong><br>
    Your invoice <strong>{{ $order->invoice->invoice_number }}</strong> is attached to this email as a PDF.
</div>
@endif

<div class="info-box">
    Your order will now be processed. You'll receive another notification when your items are delivered.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View My Order</a>
</div>
@endsection
