@extends('layouts.app')
@section('title', 'Dashboard')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-category">

    @include('partials.alerts')


    <!-- Category List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Categories</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('categories.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>

                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEcommerceCategoryList">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Category
                </button>
            </div>
        </div>

        
        <div class="table-responsive">
            <table class="table table-bordered" id="categories-table">
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

    <!-- Offcanvas to Add Category -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEcommerceCategoryList" aria-labelledby="offcanvasEcommerceCategoryListLabel">
        <div class="offcanvas-header py-6">
            <h5 id="offcanvasEcommerceCategoryListLabel" class="offcanvas-title">Add Category</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Title -->
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" 
                        class="form-control @error('name') is-invalid @enderror" 
                        name="name" 
                        value="{{ old('name') }}" 
                        placeholder="Enter category title" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" 
                        class="form-control @error('slug') is-invalid @enderror" 
                        name="slug" 
                        value="{{ old('slug') }}" 
                        placeholder="Enter slug" required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
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
let table = $('#categories-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('categories.index') }}',
    columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'slug', name: 'slug' },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});
</script>
@endpush

