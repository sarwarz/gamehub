@extends('emails.orders.layout')

@section('title', 'Order Cancelled')

@section('banner')
<div class="status-icon" style="background:#fee2e2; color:#dc2626">&#10006;</div>
<h2 class="status-title">Order Cancelled</h2>
<p class="status-sub">Order #{{ $order->order_number }}</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Your order <strong>#{{ $order->order_number }}</strong> has been cancelled.</p>

@include('emails.orders._order-meta')

@include('emails.orders._items-table', ['showDeliveryStatus' => false])

<hr class="divider">

<div class="info-box">
    @if($order->payment_status === 'paid')
    <strong>Refund:</strong> Since your order was already paid, a refund of <strong>{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</strong> will be processed. Please allow 5-10 business days for the refund to appear, depending on your payment method.
    @else
    No payment was charged for this order.
    @endif
</div>

<div class="cta-wrapper">
    <a href="{{ url('/') }}" class="cta-btn">Continue Shopping</a>
</div>
@endsection
