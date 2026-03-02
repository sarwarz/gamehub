@props(['invoice'])

<div class="dropdown">
    <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
        <i class="ti tabler-dots-vertical ti-md"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-end">
        <a class="dropdown-item" href="{{ route('invoices.show', $invoice->id) }}">
            <i class="ti tabler-eye me-2 ti-sm"></i> View
        </a>
        <a class="dropdown-item" href="{{ route('invoices.print', $invoice->id) }}" target="_blank">
            <i class="ti tabler-printer me-2 ti-sm"></i> Print
        </a>
        <a class="dropdown-item" href="{{ route('invoices.download', $invoice->id) }}">
            <i class="ti tabler-file-type-pdf me-2 ti-sm"></i> Download PDF
        </a>

        @if($invoice->status !== 'paid')
        <div class="dropdown-divider"></div>
        <form action="{{ route('invoices.mark-paid', $invoice->id) }}" method="POST" class="mark-paid-form">
            @csrf
            <button type="submit" class="dropdown-item text-success btn-mark-paid">
                <i class="ti tabler-circle-check me-2 ti-sm"></i> Mark Paid
            </button>
        </form>
        @endif

        <div class="dropdown-divider"></div>
        <a href="javascript:void(0);" class="dropdown-item text-danger delete-btn" data-url="{{ route('invoices.destroy', $invoice->id) }}">
            <i class="ti tabler-trash me-2 ti-sm"></i> Delete
        </a>
    </div>
</div>
