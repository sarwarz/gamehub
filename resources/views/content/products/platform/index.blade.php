@extends('layouts.app')
@section('title', 'Product Platforms')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-platform">

    @include('partials.alerts')

    <!-- Platforms List Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Platforms</h5>
            <div>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('platforms.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPlatformList">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Platform
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="platforms-table">
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

    <!-- Offcanvas to Add Platform -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasPlatformList" aria-labelledby="offcanvasPlatformListLabel">
        <div class="offcanvas-header py-6">
            <h5 id="offcanvasPlatformListLabel" class="offcanvas-title">Add Platform</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('platforms.store') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Platform Name</label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="Enter platform name" required>
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
let table = $('#platforms-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('platforms.index') }}',
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
