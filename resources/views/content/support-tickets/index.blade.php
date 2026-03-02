@extends('layouts.app')

@section('title', 'Support Tickets')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-support-tickets.css') }}">
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-lifebuoy me-2"></i>Support Tickets</h4>
            <p class="text-muted mb-0">Manage customer and seller support requests</p>
        </div>
        <a href="{{ route('support-tickets.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Create Ticket
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 ticket-stats">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-message-circle fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['open'] }}</h5>
                            <small class="text-muted">Open Tickets</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-danger">
                            <i class="ti tabler-urgent fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['escalated'] }}</h5>
                            <small class="text-muted">Escalated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['resolved'] }}</h5>
                            <small class="text-muted">Resolved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-secondary">
                            <i class="ti tabler-lock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['closed'] }}</h5>
                            <small class="text-muted">Closed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        {{-- Card Header: Title + Filters Toggle --}}
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-inbox me-2"></i>All Tickets</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse" aria-expanded="false">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Row --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-circle-dot ti-xs"></i></span>
                            <select class="form-select" id="filter-status">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\SupportTicket::STATUSES as $s)
                                    <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-category ti-xs"></i></span>
                            <select class="form-select" id="filter-department">
                                <option value="">All Departments</option>
                                @foreach(\App\Models\TicketDepartment::active()->orderBy('sort_order')->get() as $dept)
                                    <option value="{{ $dept->slug }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-flag ti-xs"></i></span>
                            <select class="form-select" id="filter-priority">
                                <option value="">All Priorities</option>
                                @foreach(\App\Models\SupportTicket::PRIORITIES as $p)
                                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" id="btn-apply-filters">
                            <i class="ti tabler-search me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn-clear-filters">
                            <i class="ti tabler-filter-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">tickets selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-switch-horizontal me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(\App\Models\SupportTicket::STATUSES as $s)
                                <li><a class="dropdown-item bulk-status-btn" href="javascript:void(0);" data-status="{{ $s }}">
                                    <i class="ti tabler-point-filled me-1" style="font-size:.5rem"></i> {{ ucwords(str_replace('_', ' ', $s)) }}
                                </a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-label-warning dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-flag me-1"></i> Change Priority
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(\App\Models\SupportTicket::PRIORITIES as $p)
                                <li><a class="dropdown-item bulk-priority-btn" href="javascript:void(0);" data-priority="{{ $p }}">
                                    <i class="ti tabler-point-filled me-1" style="font-size:.5rem"></i> {{ ucfirst($p) }}
                                </a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-label-info dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti tabler-user-check me-1"></i> Assign To
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->where('name','admin'))->select('id','name')->get() as $admin)
                                <li><a class="dropdown-item bulk-assign-btn" href="javascript:void(0);" data-admin-id="{{ $admin->id }}">
                                    <i class="ti tabler-user me-1"></i> {{ $admin->name }}
                                </a></li>
                                @endforeach
                            </ul>
                        </div>
                        <button class="btn btn-sm btn-label-success bulk-close-btn" type="button">
                            <i class="ti tabler-circle-check me-1"></i> Close
                        </button>
                        <button class="btn btn-sm btn-label-danger bulk-delete-btn" type="button">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="tickets-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Ticket</th>
                        <th>Customer</th>
                        <th>Department</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('page-js')
<script>
    window.ticketIndexConfig = {
        csrfToken:      '{{ csrf_token() }}',
        indexUrl:        '{{ route("support-tickets.index") }}',
        baseUrl:         '{{ url("dashboard/support-tickets") }}',
        bulkDeleteUrl:   '{{ route("support-tickets.bulk-delete") }}',
        bulkActionUrl:   '{{ route("support-tickets.bulk-action") }}'
    };
</script>
<script src="{{ asset('assets/js/app-support-ticket-index.js') }}"></script>
@endpush
