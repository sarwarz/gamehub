@extends('emails.orders.layout')

@section('title', 'New Message on Your Order')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#9993;</div>
<h2 class="status-title">New Message</h2>
<p class="status-sub">Regarding your order #{{ $order->order_number }}</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>Our team has left a message on your order:</p>

<div class="message-bubble">
    {{ $noteText }}
</div>

<p style="font-size:12px; color:#a0aec0; margin-top:4px;">— {{ $adminName }}</p>

@include('emails.orders._order-meta')

<div class="section-title">Order Items</div>
<table class="items-table" width="100%" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="text-align:left">Product</th>
            <th style="text-align:right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>
                <div class="item-name">{{ $item->product->title ?? 'Product' }}</div>
                <div class="item-meta">Qty: {{ $item->quantity }}</div>
            </td>
            <td style="text-align:right; font-weight:600">{{ format_currency($item->subtotal) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals-table" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="total-label">Subtotal</td>
        <td class="total-value">{{ format_currency($order->subtotal) }}</td>
    </tr>
    @if($order->tax_amount > 0)
    <tr>
        <td class="total-label">Tax</td>
        <td class="total-value">{{ format_currency($order->tax_amount) }}</td>
    </tr>
    @endif
    @if($order->discount_amount > 0)
    <tr>
        <td class="total-label">Discount</td>
        <td class="total-value" style="color:#dc2626">-{{ format_currency($order->discount_amount) }}</td>
    </tr>
    @endif
    <tr class="grand-total">
        <td class="total-label">Total</td>
        <td class="total-value">{{ format_currency($order->total_amount) }}</td>
    </tr>
</table>

<hr class="divider">

<div class="info-box">
    If you have any questions about this message, you can reply by visiting your order page or contacting our support team.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View My Order</a>
</div>
@endsection
