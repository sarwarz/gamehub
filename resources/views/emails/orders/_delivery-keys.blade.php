@php
    $hasDeliveries = false;
    foreach ($order->items as $item) {
        foreach ($item->deliveries as $d) {
            if ($d->status === 'delivered' && !empty($d->payload)) { $hasDeliveries = true; break 2; }
        }
    }
@endphp

@if($hasDeliveries)
<div class="section-title" style="color:#16a34a">Delivered Items &amp; Keys</div>

@foreach($order->items as $item)
    @foreach($item->deliveries as $delivery)
        @if($delivery->status === 'delivered' && !empty($delivery->payload))
        <div class="delivery-card">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <div class="dc-product">{{ $item->product->title ?? 'Product' }}</div>
                        <div class="dc-status">&#10003; Delivered {{ $delivery->delivered_at ? '· ' . $delivery->delivered_at->format('M d, Y h:i A') : '' }}</div>
                    </td>
                </tr>
                @php
                    $keys = is_array($delivery->payload) ? $delivery->payload : [$delivery->payload];
                @endphp
                @foreach($keys as $key)
                <tr>
                    <td style="padding-top:8px">
                        <div class="dc-key">{{ is_array($key) ? ($key['key'] ?? ($key['code'] ?? json_encode($key))) : $key }}</div>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif
    @endforeach
@endforeach

<div class="info-box">
    <strong>How to redeem:</strong> Copy the key above and activate it on the respective platform (Steam, Epic, etc.). If you need help, contact our support team.
</div>
@endif
