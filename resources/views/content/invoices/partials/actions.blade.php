@props([
    'invoice'
])

<div class="dropdown">
    <button
        type="button"
        class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        <i class="ti tabler-dots-vertical"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow-sm">

        {{-- View --}}
        <a class="dropdown-item" href="{{ route('invoices.show', $invoice->id) }}">
            <i class="ti tabler-eye me-1"></i>
            View Invoice
        </a>

        {{-- Print --}}
        <a class="dropdown-item" href="{{ route('invoices.print', $invoice->id) }}" target="_blank">
            <i class="ti tabler-printer me-1"></i>
            Print
        </a>

        {{-- Download PDF --}}
        <a class="dropdown-item" href="{{ route('invoices.download', $invoice->id) }}">
            <i class="ti tabler-file-type-pdf me-1"></i>
            Download PDF
        </a>

        <div class="dropdown-divider"></div>

        {{-- Mark Paid --}}
        @if($invoice->status !== 'paid')
            <form action="{{ route('invoices.mark-paid', $invoice->id) }}"
                  method="POST"
                  onsubmit="return confirm('Mark this invoice as paid?')">
                @csrf
                <button type="submit" class="dropdown-item text-success">
                    <i class="ti tabler-circle-check me-1"></i>
                    Mark as Paid
                </button>
            </form>
        @endif

        {{-- Delete --}}
        <form action="{{ route('invoices.destroy', $invoice->id) }}"
              method="POST"
              onsubmit="return confirm('This will permanently delete the invoice. Continue?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item text-danger">
                <i class="ti tabler-trash me-1"></i>
                Delete
            </button>
        </form>

    </div>
</div>
