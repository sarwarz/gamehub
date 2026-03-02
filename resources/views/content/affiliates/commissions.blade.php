@extends('layouts.app')
@section('title', 'Affiliate Commissions')

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
            <h4 class="mb-1"><i class="ti tabler-coins me-2"></i>Affiliate Commissions</h4>
            <p class="text-muted mb-0">Track and manage all affiliate commission earnings</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-coins fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">${{ number_format($stats['total_earned'], 2) }}</h5>
                            <small class="text-muted">Total Earned</small>
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
                            <h5 class="mb-0">${{ number_format($stats['pending'], 2) }}</h5>
                            <small class="text-muted">Pending</small>
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
                            <h5 class="mb-0">${{ number_format($stats['available'], 2) }}</h5>
                            <small class="text-muted">Available</small>
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
                            <i class="ti tabler-arrow-back-up fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">${{ number_format($stats['reversed'], 2) }}</h5>
                            <small class="text-muted">Reversed</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Commissions</h5>
                <div class="d-flex align-items-center gap-2">
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
                                <option value="held">Held</option>
                                <option value="available">Available</option>
                                <option value="paid">Paid</option>
                                <option value="reversed">Reversed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Level</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-hierarchy ti-xs"></i></span>
                            <select class="form-select form-select-sm" id="filter-level">
                                <option value="">All Levels</option>
                                <option value="1">Level 1 (Direct)</option>
                                <option value="2">Level 2 (Sub-affiliate)</option>
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

        {{-- Table --}}
        <div class="table-responsive">
            <table id="commissions-table" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Affiliate</th>
                        <th>Order</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Rate</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th class="text-center" width="100">Actions</th>
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

const CommissionsPage = {
    table: null,

    init() {
        this.initDataTable();
        this.bindEvents();
    },

    initDataTable() {
        this.table = $('#commissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("affiliate-commissions.index") }}',
                data: d => {
                    d.status = $('#filter-status').val();
                    d.level  = $('#filter-level').val();
                }
            },
            order: [[6, 'desc']],
            lengthMenu: [10, 25, 50, 100],
            pageLength: 25,
            columns: [
                { data: 'affiliate_col', orderable: false, searchable: true },
                { data: 'order_col', orderable: false, searchable: true },
                { data: 'amount_col', name: 'amount', className: 'text-end' },
                { data: 'rate_col', orderable: false, searchable: false, className: 'text-center' },
                { data: 'level_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'date_col', name: 'created_at' },
                { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                emptyTable: '<div class="py-4 text-center"><i class="ti tabler-cash-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No commissions found</span></div>',
                zeroRecords: '<div class="py-3 text-center text-muted">No matching commissions</div>'
            }
        });
    },

    bindEvents() {
        const self = this;

        $('#apply-filters').on('click', () => self.table.ajax.reload());

        $('#clear-filters').on('click', () => {
            $('#filter-status, #filter-level').val('');
            self.table.ajax.reload();
        });

        $(document).on('click', '.btn-release', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Release this commission?',
                text: 'The commission will be made available for withdrawal.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, release',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(self.actionUrl(id, 'release'))
                    .done(() => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Commission released', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not release commission.', timer: 2000, showConfirmButton: false }));
            });
        });

        $(document).on('click', '.btn-reverse', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Reverse this commission?',
                text: 'This will mark the commission as reversed and deduct it from the affiliate balance.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reverse',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.post(self.actionUrl(id, 'reverse'))
                    .done(() => {
                        self.table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Commission reversed', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                    })
                    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not reverse commission.', timer: 2000, showConfirmButton: false }));
            });
        });
    },

    actionUrl(id, action) {
        return '{{ url("dashboard/affiliate-commissions") }}/' + id + '/' + action;
    }
};

$(document).ready(() => CommissionsPage.init());

})(jQuery);
</script>
@endpush
