@extends('emails.orders.layout')

@section('title', 'Order Confirmed')

@section('banner')
<div class="status-icon" style="background:#dcfce7; color:#16a34a">&#10004;</div>
<h2 class="status-title">Order Confirmed!</h2>
<p class="status-sub">Thank you for your purchase</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Your order has been placed successfully. We're getting everything ready for you.</p>

@include('emails.orders._order-meta')

@include('emails.orders._items-table', ['showDeliveryStatus' => false])

<hr class="divider">

<div class="info-box">
    We will send you an email once your order is processed and your items are delivered. Keep an eye on your inbox!
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View My Order</a>
</div>
@endsection
