
<a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-warning">
    Edit
</a>

<button
    type="button"
    class="btn btn-sm btn-danger btn-delete"
    data-url="{{ route('orders.destroy', $order->id) }}"
>
    Delete
</button>
