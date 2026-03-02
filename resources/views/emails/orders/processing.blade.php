@extends('emails.orders.layout')

@section('title', 'Order Processing')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#9881;</div>
<h2 class="status-title">Order Processing</h2>
<p class="status-sub">We're working on your order</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Your order <strong>#{{ $order->order_number }}</strong> is now being processed. Our team is preparing your items for delivery.</p>

@include('emails.orders._order-meta')

@include('emails.orders._items-table', ['showDeliveryStatus' => true])

<hr class="divider">

<div class="info-box">
    You'll receive a confirmation email with your product keys once delivery is complete. This usually takes just a few minutes for digital products.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">Track My Order</a>
</div>
@endsection
