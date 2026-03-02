@extends('layouts.app')
@section('title', 'Affiliates')

@push('page-css')
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-affiliate me-2"></i>Affiliates</h4>
            <p class="text-muted mb-0">Manage your affiliate partners and referral program</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-affiliate fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Affiliates</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-success">
                            <i class="ti tabler-circle-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['active'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-warning">
                            <i class="ti tabler-clock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['pending'] }}</h5>
                            <small class="text-muted">Pending</small>
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
                            <i class="ti tabler-ban fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['suspended'] }}</h5>
                            <small class="text-muted">Suspended</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Affiliates</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-label-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ti tabler-settings-2 me-1 ti-xs"></i> Bulk Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Change Status</h6></li>
                            <li>
                                <a class="dropdown-item bulk-status-action" href="#" data-status="active">
                                    <i class="ti tabler-circle-check ti-xs me-2 text-success"></i> Approve
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item bulk-status-action" href="#" data-status="suspended">
                                    <i class="ti tabler-ban ti-xs me-2 text-danger"></i> Suspend
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="bulk-delete">
                                    <i class="ti tabler-trash ti-xs me-2"></i> Delete Selected
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            {{-- Collapsible Filters --}}
            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-filter ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Tier</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-star ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-tier">
                                <option value="">All Tiers</option>
                                @foreach($tiers as $tier)
                                    <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="apply-filters">
                            <i class="ti tabler-check me-1 ti-xs"></i> Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear-filters">
                            <i class="ti tabler-x me-1 ti-xs"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none px-3 py-2 my-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">affiliates selected</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="affiliates-table" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>User</th>
                        <th>Code</th>
                        <th>Tier</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Balance</th>
                        <th class="text-end">Earned</th>
                        <th>Joined</th>
                        <th class="text-center" width="120">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('page-js')
<script>
(function($) {
'use strict';

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const AffiliatesPage = {
    table: null,

    init() {
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#affiliates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("affiliates.index") }}',
                data: d => {
                    d.status = $('#filter-status').val();
                    d.tier   = $('#filter-tier').val();
                }
            },
            order: [[7, 'desc']],
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, className: 'text-center pe-0' },
                { data: 'user_col', orderable: false, searchable: true },
                { data: 'code_col', name: 'referral_code' },
                { data: 'tier_badge', orderable: false, searchable: false },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'balance_col', orderable: false, searchable: false, className: 'text-end' },
                { data: 'earned_col', orderable: false, searchable: false, className: 'text-end' },
                { data: 'date_col', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            drawCallback: () => this.syncBulkBar(),
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-users-minus ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No affiliates found</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching affiliates</div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        $('#apply-filters').on('click', () => self.table.ajax.reload());

        $('#clear-filters').on('click', () => {
            $('#filter-status, #filter-tier').val('');
            self.table.ajax.reload();
        });

        $('#select-all').on('change', function() {
            $('.bulk-checkbox').prop('checked', this.checked);
            self.syncBulkBar();
        });

        $(document).on('change', '.bulk-checkbox', () => self.syncBulkBar());

        $(document).on('click', '.bulk-status-action', function(e) {
            e.preventDefault();
            self.handleBulkStatus($(this).data('status'));
        });

        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            self.handleBulkDelete();
        });

        $(document).on('click', '.btn-approve', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Approve this affiliate?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(self.approveUrl(id))
                    .done(() => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Affiliate approved', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not approve affiliate.', timer: 2000, showConfirmButton: false }));
            });
        });

        $(document).on('click', '.btn-suspend', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Suspend this affiliate?',
                text: 'The affiliate will lose access to their referral program.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, suspend',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-warning me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(self.suspendUrl(id))
                    .done(() => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Affiliate suspended', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not suspend affiliate.', timer: 2000, showConfirmButton: false }));
            });
        });

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete this affiliate?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.ajax({
                    url: self.destroyUrl(id),
                    type: 'DELETE'
                })
                .done(() => {
                    self.table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Affiliate deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not delete affiliate.', timer: 2000, showConfirmButton: false }));
            });
        });
    },

    approveUrl(id) {
        return '{{ route("affiliates.approve", ":id") }}'.replace(':id', id);
    },

    suspendUrl(id) {
        return '{{ route("affiliates.suspend", ":id") }}'.replace(':id', id);
    },

    destroyUrl(id) {
        return '{{ route("affiliates.destroy", ":id") }}'.replace(':id', id);
    },

    syncBulkBar() {
        const count = $('.bulk-checkbox:checked').length;
        const $bar = $('#bulk-bar');
        $('#bulk-count').text(count);
        if (count > 0) {
            $bar.removeClass('d-none');
        } else {
            $bar.addClass('d-none');
        }
        const total = $('.bulk-checkbox').length;
        $('#select-all').prop('checked', count > 0 && count === total);
    },

    getSelectedIds() {
        return $('.bulk-checkbox:checked').map((_, el) => el.value).get();
    },

    handleBulkStatus(status) {
        const ids = this.getSelectedIds();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one affiliate.', timer: 2000, showConfirmButton: false });
            return;
        }
        const label = status === 'active' ? 'approved' : status;
        Swal.fire({
            title: 'Change Affiliate Status?',
            html: `<span class="text-muted">Selected affiliates will be marked as <strong>${label}</strong>.</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post('{{ route("affiliates.bulk-status") }}', { ids, status })
                .done(() => {
                    this.afterBulkAction();
                    Swal.fire({ icon: 'success', title: 'Status updated', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not update status.', timer: 2000, showConfirmButton: false }));
        });
    },

    handleBulkDelete() {
        const ids = this.getSelectedIds();
        if (!ids.length) {
            Swal.fire({ icon: 'info', title: 'No Selection', text: 'Please select at least one affiliate.', timer: 2000, showConfirmButton: false });
            return;
        }
        Swal.fire({
            title: 'Delete selected affiliates?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;
            $.post('{{ route("affiliates.bulk-delete") }}', { ids })
                .done(() => {
                    this.afterBulkAction();
                    Swal.fire({ icon: 'success', title: 'Affiliates deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                })
                .fail(() => Swal.fire({ icon: 'error', title: 'Failed', text: 'Could not delete affiliates.', timer: 2000, showConfirmButton: false }));
        });
    },

    afterBulkAction() {
        this.table.ajax.reload(null, false);
        $('#select-all').prop('checked', false);
        this.syncBulkBar();
    }
};

$(document).ready(() => AffiliatesPage.init());

})(jQuery);
</script>
@endpush
