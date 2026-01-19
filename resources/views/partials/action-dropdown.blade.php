@props([
    'editUrl' => null,

    'deleteId' => null,
    'deleteClass' => 'delete-btn',

    // Status toggle
    'showStatusToggle' => false,
    'isActive' => false,
    'toggleClass' => 'status-toggle-btn',
    'toggleId' => null,
])
<div class="dropdown">
    <button
        type="button"
        class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
        data-bs-toggle="dropdown"
        aria-expanded="false">
        <i class="ti tabler-dots-vertical"></i>
    </button>

    <div class="dropdown-menu">

        {{-- Edit --}}
        @if($editUrl)
            <a class="dropdown-item" href="{{ $editUrl }}">
                <i class="ti tabler-edit me-1"></i> Edit
            </a>
        @endif

        {{-- Active / Inactive --}}
        @if($showStatusToggle)
            <a href="javascript:void(0);"
               class="dropdown-item {{ $toggleClass }}"
               data-id="{{ $toggleId }}"
               data-status="{{ $isActive ? 'inactive' : 'active' }}">

                @if($isActive)
                    <i class="ti tabler-circle-x me-1 text-warning"></i>
                    Deactivate
                @else
                    <i class="ti tabler-circle-check me-1 text-success"></i>
                    Activate
                @endif
            </a>
        @endif

        {{-- Delete --}}
        @if($deleteId)
            <a href="javascript:void(0);"
               class="dropdown-item text-danger {{ $deleteClass }}"
               data-id="{{ $deleteId }}">
                <i class="ti tabler-trash me-1"></i> Delete
            </a>
        @endif

    </div>
</div>
