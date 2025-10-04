@extends('layouts.app')
@section('title', 'Product Regions')

@section('content')
<div class="app-ecommerce-regions">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Regions</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('regions.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRegionList">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Region
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="regions-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Offcanvas Add -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRegionList">
        <div class="offcanvas-header py-6">
            <h5 class="offcanvas-title">Add Product Region</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('regions.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Region Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter region name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="Enter slug" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
let table = $('#regions-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('regions.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'name' },
        { data: 'slug' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush
