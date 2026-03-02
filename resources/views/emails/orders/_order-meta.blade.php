<div class="order-meta">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label" style="padding:4px 0">Order Number</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">#{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Date</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ $order->created_at?->format('M d, Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Payment</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0">Status</td>
            <td class="meta-value" style="padding:4px 0; text-align:right">
                @php
                    $sMap = ['pending'=>'badge-warning','processing'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger','refunded'=>'badge-purple'];
                    $sClass = $sMap[$order->status] ?? 'badge-info';
                @endphp
                <span class="badge {{ $sClass }}">{{ ucfirst($order->status) }}</span>
            </td>
        </tr>
    </table>
</div>
