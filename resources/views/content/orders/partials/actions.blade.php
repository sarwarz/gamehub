@include('partials.action-dropdown', [
    'editUrl'   => route('orders.edit', $order->id),
    'deleteUrl' => route('orders.destroy', $order->id),
    'deleteId'  => $order->id,
])
