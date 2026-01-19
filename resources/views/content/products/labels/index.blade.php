@extends('layouts.app')
@section('title', 'Product Labels')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-category">

    @include('partials.alerts')

    <!-- Labels List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Labels</h5>
            <div>
                <button class="btn btn-danger"
                        id="bulk-delete"
                        data-url="{{ route('labels.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i>
                    Delete Selected
                </button>

                <button class="btn btn-primary"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#offcanvasProductLabel">
                    <i class="menu-icon icon-base ti tabler-plus"></i>
                    Add Label
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="labels-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Name</th>
                        <th>Preview</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Offcanvas: Add Product Label -->
    <div class="offcanvas offcanvas-end"
         tabindex="-1"
         id="offcanvasProductLabel"
         aria-labelledby="offcanvasProductLabelLabel">

        <div class="offcanvas-header py-6">
            <h5 id="offcanvasProductLabelLabel" class="offcanvas-title">
                Add Product Label
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('labels.store') }}">
                @csrf

                <!-- Label Name -->
                <div class="mb-3">
                    <label class="form-label">Label Name</label>
                    <input type="text"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           placeholder="e.g. Hot, New, Sale"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Background Color -->
                <div class="mb-3">
                    <label class="form-label">Background Color</label>
                    <input type="color"
                           name="bg_color"
                           class="form-control form-control-color @error('bg_color') is-invalid @enderror"
                           value="{{ old('bg_color', '#ff0000') }}">
                    @error('bg_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Text Color -->
                <div class="mb-3">
                    <label class="form-label">Text Color</label>
                    <input type="color"
                           name="text_color"
                           class="form-control form-control-color @error('text_color') is-invalid @enderror"
                           value="{{ old('text_color', '#ffffff') }}">
                    @error('text_color')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status"
                            class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                    <button type="button"
                            class="btn btn-label-danger"
                            data-bs-dismiss="offcanvas">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(function () {

    const table = $('#labels-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('labels.index') }}',
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'label_preview', orderable: false, searchable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    // Select all
    $('#select-all').on('click', function () {
        $('.bulk-checkbox').prop('checked', this.checked);
    });

});
</script>
@endpush
