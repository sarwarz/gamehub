@extends('layouts.app')
@section('title', 'Sliders')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-sliders">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Sliders</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete"
                        data-url="{{ route('sliders.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>

                <a class="btn btn-primary" href="{{ route('sliders.create') }}">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Slider
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="sliders-table">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>Slider</th>
                        <th>Product</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection
@push('page-js')
<script>
let table = $('#sliders-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('sliders.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'slider_column', name: 'title', searchable: true },
        { data: 'product', name: 'product.title', searchable: true },
        { data: 'position', name: 'position' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ],
    columnDefs: [
        { targets: [0,3,4,5], className: "text-center" }
    ]
});

// Select all
$('#select-all').on('click', function () {
    $('.row-checkbox').prop('checked', this.checked);
});

$(document).on('click', '.delete-btn', function () {
    let id = $(this).data('id');

    if (!confirm('Delete this slider?')) return;

    $.ajax({
        url: `/dashboard/sliders/${id}`,
        type: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: () => table.ajax.reload()
    });
});

</script>
@endpush
