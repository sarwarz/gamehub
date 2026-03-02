@extends('emails.orders.layout')

@section('title', 'Order Refunded')

@section('banner')
<div class="status-icon" style="background:#ede9fe; color:#7c3aed">&#8634;</div>
<h2 class="status-title">Refund Issued</h2>
<p class="status-sub">Order #{{ $order->order_number }}</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $customerName }}</strong>,</p>
<p>A refund has been issued for your order <strong>#{{ $order->order_number }}</strong>.</p>

@include('emails.orders._order-meta')

@include('emails.orders._items-table', ['showDeliveryStatus' => false])

<hr class="divider">

<div class="order-meta" style="background:#faf5ff; border:1px solid #e9d5ff">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label" style="padding:4px 0; color:#7c3aed">Refund Amount</td>
            <td class="meta-value" style="padding:4px 0; text-align:right; color:#7c3aed; font-size:18px">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Payment Method</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Refund Date</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ now()->format('M d, Y') }}</td>
        </tr>
    </table>
</div>

<div class="info-box">
    The refund should appear in your account within <strong>5-10 business days</strong> depending on your payment provider. If you used wallet credit, it has been restored to your wallet balance immediately.
</div>

<div class="cta-wrapper">
    <a href="{{ url('/') }}" class="cta-btn">Continue Shopping</a>
</div>
@endsection
