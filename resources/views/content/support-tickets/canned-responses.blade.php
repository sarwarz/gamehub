@extends('layouts.app')

@section('title', 'Canned Responses')

@section('content')

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-0"><i class="ti tabler-template me-1"></i> Canned Responses</h4>
            <p class="text-muted mb-0 mt-1">Predefined reply templates for support tickets</p>
        </div>
        <button class="btn btn-primary" id="btn-add-new">
            <i class="ti tabler-plus me-1"></i> New Template
        </button>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select class="form-select form-select-sm" id="filter-category">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\CannedResponse::CATEGORIES as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-secondary" id="btn-reset-filters">
                        <i class="ti tabler-refresh me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover" id="canned-table" style="width:100%">
                <thead>
                    <tr>
                        <th>Template</th>
                        <th>Preview</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th style="width:80px">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Placeholders info --}}
    <div class="card mt-4">
        <div class="card-header py-3">
            <h6 class="mb-0"><i class="ti tabler-code me-1 text-primary"></i> Available Placeholders</h6>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                <div class="col-md-4"><code>{customer_name}</code> <span class="text-muted">— Customer's name</span></div>
                <div class="col-md-4"><code>{order_number}</code> <span class="text-muted">— Related order #</span></div>
                <div class="col-md-4"><code>{agent_name}</code> <span class="text-muted">— Current agent name</span></div>
                <div class="col-md-4"><code>{amount}</code> <span class="text-muted">— Refund/charge amount</span></div>
                <div class="col-md-4"><code>{reason}</code> <span class="text-muted">— Reason placeholder</span></div>
                <div class="col-md-4"><code>{carrier}</code> <span class="text-muted">— Shipping carrier</span></div>
                <div class="col-md-4"><code>{tracking}</code> <span class="text-muted">— Tracking number</span></div>
                <div class="col-md-4"><code>{timeframe}</code> <span class="text-muted">— Time estimate</span></div>
            </div>
        </div>
    </div>

{{-- Offcanvas Form --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="formOffcanvas" style="width:460px">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title" id="offcanvas-title"><i class="ti tabler-template me-2"></i>New Template</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form id="canned-form">
            <input type="hidden" id="form-id" value="">

            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="form-title" name="title" required placeholder="e.g. Welcome Greeting">
            </div>

            <div class="mb-3">
                <label class="form-label">Shortcut</label>
                <div class="input-group">
                    <span class="input-group-text">/</span>
                    <input type="text" class="form-control" id="form-shortcut" name="shortcut" placeholder="e.g. hello">
                </div>
                <small class="text-muted">Type this shortcut in the reply box + press Space to auto-expand</small>
            </div>

            <div class="row mb-3">
                <div class="col-7">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="form-category" name="category" required>
                        @foreach(\App\Models\CannedResponse::CATEGORIES as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-5">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" id="form-sort" name="sort_order" value="0" min="0">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Body <span class="text-danger">*</span></label>
                <textarea class="form-control" id="form-body" name="body" rows="8" required placeholder="Type your template text here. Use placeholders like {customer_name}..."></textarea>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="form-active" name="is_active" checked>
                    <label class="form-check-label" for="form-active">Active</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1" id="btn-save">
                    <i class="ti tabler-check me-1"></i> Save Template
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
    window.cannedConfig = {
        csrfToken: '{{ csrf_token() }}',
        baseUrl:   '{{ route("canned-responses.index") }}'
    };
</script>
<script src="{{ asset('assets/js/app-canned-responses.js') }}"></script>
@endpush
