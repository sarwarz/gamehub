@extends('emails.orders.layout')

@section('title', 'New Order Received')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#128176;</div>
<h2 class="status-title">New Order Received</h2>
<p class="status-sub">Order #{{ $order->order_number }}</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $recipientName }}</strong>,</p>
<p>A new order has been placed on the platform.</p>

<div class="order-meta">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label" style="padding:4px 0">Order Number</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">#{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Customer</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ $order->user->name ?? 'Guest' }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Email</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ $order->user->email ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Payment</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }} · <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span></td>
        </tr>
    </table>
</div>

@include('emails.orders._items-table', ['showDeliveryStatus' => false])

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View Order in Dashboard</a>
</div>
@endsection
