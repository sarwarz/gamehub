@extends('layouts.app')

@section('title', 'Subscribers')

@push('page-css')
<style>
.subscriber-stats .avatar {
    display: flex;
    align-items: center;
    justify-content: center;
}
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-mail-bolt me-2"></i>Subscribers</h4>
            <p class="text-muted mb-0">Manage newsletter subscribers</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 subscriber-stats">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-users fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-total">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Subscribers</small>
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
                            <i class="ti tabler-mail-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-active">{{ $stats['active'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-mail-off fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-unsub">{{ $stats['unsubscribed'] }}</h5>
                            <small class="text-muted">Unsubscribed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-calendar-plus fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0" id="stat-month">{{ $stats['this_month'] }}</h5>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Subscribers</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                    <button class="btn btn-outline-secondary btn-sm btn-export" type="button">
                        <i class="ti tabler-download me-1"></i> Export CSV
                    </button>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#subscriberOffcanvas">
                        <i class="ti tabler-plus me-1"></i> Add Subscriber
                    </button>
                </div>
            </div>

            {{-- Collapsible Filter Panel --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select form-select-sm" id="filter-status">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="unsubscribed">Unsubscribed</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="applyFilters">
                                <i class="ti tabler-check me-1"></i> Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="clearFilters">
                                <i class="ti tabler-x me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">subscribers selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-danger btn-bulk-delete">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="subscribers-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Subscriber</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Subscribed</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Offcanvas Form --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="subscriberOffcanvas" style="width:420px;">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas-title">Add Subscriber</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <form id="subscriber-form">
                <input type="hidden" id="edit-id" value="">

                <div class="mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                        <input type="email" class="form-control" id="sub-email" name="email" required placeholder="subscriber@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-user"></i></span>
                        <input type="text" class="form-control" id="sub-name" name="name" placeholder="Optional name">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-check me-1"></i> <span id="btn-submit-text">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
$(function() {
    const csrfToken = '{{ csrf_token() }}';

    const table = $('#subscribers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("subscribers.index") }}',
            data: function(d) {
                d.filter_status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false },
            { data: 'subscriber_info', name: 'email' },
            { data: 'status_badge', name: 'status' },
            { data: 'ip_address', name: 'ip_address' },
            { data: 'date_col', name: 'subscribed_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[4, 'desc']],
        pageLength: 15,
    });

    // Filter handlers
    $('#applyFilters').on('click', function() {
        table.ajax.reload();
        if ($('#filter-status').val()) {
            $('#clearFilters').removeClass('d-none');
        }
    });
    $('#clearFilters').on('click', function() {
        $('#filter-status').val('');
        $(this).addClass('d-none');
        table.ajax.reload();
    });

    // Select all / Bulk bar
    $('#select-all').on('change', function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulk();
    });
    $(document).on('change', '.row-checkbox', updateBulk);
    function updateBulk() {
        const count = $('.row-checkbox:checked').length;
        $('#bulk-count').text(count);
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none');
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    // Add/Edit form submit
    $('#subscriber-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit-id').val();
        const url = id ? '{{ url("subscribers") }}/' + id : '{{ route("subscribers.store") }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url, type: method,
            data: { email: $('#sub-email').val(), name: $('#sub-name').val(), _token: csrfToken },
            success: function(res) {
                bootstrap.Offcanvas.getInstance(document.getElementById('subscriberOffcanvas')).hide();
                table.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                resetForm();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {}).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Error', html: msg });
            }
        });
    });

    function resetForm() {
        $('#edit-id').val('');
        $('#sub-email').val('');
        $('#sub-name').val('');
        $('#offcanvas-title').text('Add Subscriber');
        $('#btn-submit-text').text('Save');
    }
    $('#subscriberOffcanvas').on('hidden.bs.offcanvas', resetForm);

    // Toggle status
    $(document).on('click', '.btn-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        const action = status === 'active' ? 'unsubscribe' : 'resubscribe';
        Swal.fire({
            title: 'Confirm',
            text: `Are you sure you want to ${action} this subscriber?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("subscribers") }}/' + id,
                    type: 'PUT',
                    data: { _quick: 1, _token: csrfToken },
                    success: function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Subscriber?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("subscribers") }}/' + id,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function(res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    }
                });
            }
        });
    });

    // Bulk delete
    $('.btn-bulk-delete').on('click', function() {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        Swal.fire({
            title: `Delete ${ids.length} subscribers?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete All',
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("subscribers.bulk-delete") }}', { ids, _token: csrfToken }, function(res) {
                    table.ajax.reload(null, false);
                    updateBulk();
                    $('#select-all').prop('checked', false);
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                });
            }
        });
    });

    // Export
    $('.btn-export').on('click', function() {
        window.location.href = '{{ route("subscribers.export") }}';
    });
});
</script>
@endpush
