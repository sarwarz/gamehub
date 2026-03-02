@extends('layouts.app')

@section('title', 'Ticket Departments')

@section('content')

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-0"><i class="ti tabler-category me-1"></i> Ticket Departments</h4>
            <p class="text-muted mb-0 mt-1">Manage support ticket departments / categories</p>
        </div>
        <button class="btn btn-primary" id="btn-add-new">
            <i class="ti tabler-plus me-1"></i> New Department
        </button>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover" id="dept-table" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Color</th>
                        <th class="text-center">Tickets</th>
                        <th class="text-center">Status</th>
                        <th>Order</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Available Colors & Icons reference --}}
    <div class="card mt-4">
        <div class="card-header py-3">
            <h6 class="mb-0"><i class="ti tabler-palette me-1 text-primary"></i> Available Badge Colors</h6>
        </div>
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2">
                @foreach(['primary','secondary','success','danger','warning','info','dark'] as $c)
                <span class="badge bg-label-{{ $c }}">{{ $c }}</span>
                @endforeach
            </div>
        </div>
    </div>

{{-- Offcanvas Form --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="formOffcanvas" style="width:460px">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title" id="offcanvas-title"><i class="ti tabler-category me-2"></i>New Department</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form id="dept-form">
            <input type="hidden" id="form-id" value="">

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="form-name" name="name" required placeholder="e.g. Billing & Payments">
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" class="form-control" id="form-slug" name="slug" placeholder="auto-generated from name">
                <small class="text-muted">Leave empty to auto-generate from the name</small>
            </div>

            <div class="row mb-3">
                <div class="col-6">
                    <label class="form-label">Badge Color <span class="text-danger">*</span></label>
                    <select class="form-select" id="form-color" name="color" required>
                        @foreach(['primary','secondary','success','danger','warning','info','dark'] as $c)
                        <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" id="form-sort" name="sort_order" value="0" min="0">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Icon Class</label>
                <div class="input-group">
                    <span class="input-group-text" id="icon-preview"><i class="ti tabler-folder"></i></span>
                    <input type="text" class="form-control" id="form-icon" name="icon" placeholder="ti tabler-folder" value="ti tabler-folder">
                </div>
                <small class="text-muted">Use Tabler icons, e.g. <code>ti tabler-shopping-cart</code></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="form-description" name="description" rows="3" placeholder="Optional description..."></textarea>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="form-active" name="is_active" checked>
                    <label class="form-check-label" for="form-active">Active</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" id="btn-save">
                    <i class="ti tabler-check me-1"></i> Save Department
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
    window.deptConfig = {
        csrfToken: '{{ csrf_token() }}',
        baseUrl:   '{{ route("ticket-departments.index") }}'
    };
</script>
<script src="{{ asset('assets/js/app-ticket-departments.js') }}"></script>
@endpush
