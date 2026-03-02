@props([
    'viewUrl' => null,
    'editUrl' => null,

    'deleteId' => null,
    'deleteUrl' => null,
    'deleteClass' => 'delete-btn',

    'showStatusToggle' => false,
    'isActive' => false,
    'toggleClass' => 'status-toggle-btn',
    'toggleId' => null,

    'approveUrl' => null,
    'rejectUrl' => null,
])
<div class="dropdown">
    <button
        type="button"
        class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        <i class="ti tabler-dots-vertical ti-md"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-end">

        {{-- View --}}
        @if($viewUrl)
            <a class="dropdown-item" href="{{ $viewUrl }}">
                <i class="ti tabler-eye me-2 ti-sm"></i> View
            </a>
        @endif

        {{-- Edit --}}
        @if($editUrl)
            <a class="dropdown-item" href="{{ $editUrl }}">
                <i class="ti tabler-edit me-2 ti-sm"></i> Edit
            </a>
        @endif

        {{-- Active / Inactive --}}
        @if($showStatusToggle)
            <a href="javascript:void(0);"
               class="dropdown-item {{ $toggleClass }}"
               data-id="{{ $toggleId }}"
               data-status="{{ $isActive ? 'inactive' : 'active' }}">
                @if($isActive)
                    <i class="ti tabler-circle-x me-2 ti-sm text-warning"></i> Deactivate
                @else
                    <i class="ti tabler-circle-check me-2 ti-sm text-success"></i> Activate
                @endif
            </a>
        @endif

        {{-- Approve --}}
        @if($approveUrl)
            <a href="javascript:void(0);"
               class="dropdown-item text-success btn-approve"
               data-url="{{ $approveUrl }}">
                <i class="ti tabler-check me-2 ti-sm"></i> Approve
            </a>
        @endif

        {{-- Reject --}}
        @if($rejectUrl)
            <a href="javascript:void(0);"
               class="dropdown-item text-danger btn-reject"
               data-url="{{ $rejectUrl }}">
                <i class="ti tabler-x me-2 ti-sm"></i> Reject
            </a>
        @endif

        @if(($viewUrl || $editUrl || $showStatusToggle) && ($deleteId || $deleteUrl))
            <div class="dropdown-divider"></div>
        @endif

        {{-- Delete --}}
        @if($deleteId || $deleteUrl)
            <a href="javascript:void(0);"
               class="dropdown-item text-danger {{ $deleteClass }}"
               data-id="{{ $deleteId }}"
               @if($deleteUrl) data-url="{{ $deleteUrl }}" @endif>
                <i class="ti tabler-trash me-2 ti-sm"></i> Delete
            </a>
        @endif

    </div>
</div>
