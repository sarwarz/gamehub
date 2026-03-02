<div class="section-title">Order Items</div>
<table class="items-table" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="width:50%">Product</th>
            <th style="width:15%; text-align:center">Qty</th>
            <th style="width:15%; text-align:center">Price</th>
            <th style="width:20%">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>
                <div class="item-name">{{ $item->product->title ?? 'Product' }}</div>
                @if($showDeliveryStatus ?? false)
                <div class="item-meta">
                    @php $ds = $item->delivery_status ?? 'pending'; @endphp
                    @if($ds === 'delivered')
                        <span class="badge badge-success">Delivered</span>
                    @elseif($ds === 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @else
                        <span class="badge badge-info">{{ ucfirst($ds) }}</span>
                    @endif
                </div>
                @endif
            </td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:center">{{ $order->currency }} {{ number_format($item->unit_price, 2) }}</td>
            <td style="text-align:right; font-weight:600">{{ $order->currency }} {{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals-table" cellpadding="0" cellspacing="0">
    <tr>
        <td class="total-label">Subtotal</td>
        <td class="total-value">{{ $order->currency }} {{ number_format($order->subtotal, 2) }}</td>
    </tr>
    @if($order->tax_amount > 0)
    <tr>
        <td class="total-label">Tax</td>
        <td class="total-value">{{ $order->currency }} {{ number_format($order->tax_amount, 2) }}</td>
    </tr>
    @endif
    @if($order->discount_amount > 0)
    <tr>
        <td class="total-label">Discount</td>
        <td class="total-value" style="color:#16a34a">-{{ $order->currency }} {{ number_format($order->discount_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="grand-total">
        <td class="total-label">Total</td>
        <td class="total-value">{{ $order->currency }} {{ number_format($order->total_amount, 2) }}</td>
    </tr>
</table>
