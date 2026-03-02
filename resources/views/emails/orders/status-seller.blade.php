@extends('emails.orders.layout')

@section('title', 'Order Status Update')

@section('banner')
@php
    $icons = ['processing'=>'&#9881;','completed'=>'&#127881;','cancelled'=>'&#10006;','refunded'=>'&#8634;'];
    $colors = ['processing'=>['#dbeafe','#2563eb'],'completed'=>['#dcfce7','#16a34a'],'cancelled'=>['#fee2e2','#dc2626'],'refunded'=>['#ede9fe','#7c3aed']];
    $ic = $icons[$status] ?? '&#9432;';
    $bg = $colors[$status][0] ?? '#f0f0f5';
    $fg = $colors[$status][1] ?? '#718096';
@endphp
<div class="status-icon" style="background:{{ $bg }}; color:{{ $fg }}">{!! $ic !!}</div>
<h2 class="status-title">Order {{ ucfirst($status) }}</h2>
<p class="status-sub">Order #{{ $order->order_number }}</p>
@endsection

@section('content')
<p class="greeting">Hi <strong>{{ $recipientName }}</strong>,</p>
<p>Order <strong>#{{ $order->order_number }}</strong> status has changed to <strong>{{ ucfirst($status) }}</strong>.</p>

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
            <td class="meta-label" style="padding:4px 0">Total</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">New Status</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">
                @php $sMap = ['processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger','refunded'=>'badge-purple']; @endphp
                <span class="badge {{ $sMap[$status] ?? 'badge-info' }}">{{ ucfirst($status) }}</span>
            </td>
        </tr>
    </table>
</div>

@include('emails.orders._items-table', ['showDeliveryStatus' => true])

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">{{ $audience === 'admin' ? 'View Order in Dashboard' : 'View in Dashboard' }}</a>
</div>
@endsection
