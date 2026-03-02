@extends('layouts.app')
@section('title', 'Currency Management')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}">
<style>
    .currency-stats .stat-card {
        padding: 1.25rem;
        border-radius: .5rem;
        transition: transform .15s;
    }
    .currency-stats .stat-card:hover { transform: translateY(-2px); }
    .currency-stats .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .offcanvas-form-section { border-left: 3px solid #7367f0; padding-left: .75rem; margin-bottom: 1.25rem; }
    .bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
    @keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-coin me-2"></i>Currency Management</h4>
            <p class="text-muted mb-0">Manage supported currencies and exchange rates</p>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4 currency-stats">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-primary">
                        <i class="ti tabler-currency-dollar ti-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0" id="stat-total">0</h5>
                        <small class="text-muted">Total Currencies</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-success">
                        <i class="ti tabler-check ti-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0" id="stat-active">0</h5>
                        <small class="text-muted">Active</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-warning">
                        <i class="ti tabler-star ti-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0" id="stat-default">—</h5>
                        <small class="text-muted">Default Currency</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3 bg-label-info">
                        <i class="ti tabler-refresh ti-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0" id="stat-last-sync">Never</h5>
                        <small class="text-muted">Last Rate Sync</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
            <div>
                <h5 class="mb-1">
                    <i class="ti tabler-coin me-1 text-primary"></i> Currencies
                </h5>
                <p class="text-muted mb-0 small">Manage supported currencies and exchange rates</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-warning btn-sm" id="update-rates-btn" data-url="{{ route('currencies.updateRates') }}">
                    <i class="ti tabler-refresh me-1"></i> Sync Rates
                </button>
                <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCurrencyForm">
                    <i class="ti tabler-plus me-1"></i> Add Currency
                </button>
            </div>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2 px-3 mt-3" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="selected-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">currencies selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-label-danger" id="bulk-delete-btn" data-url="{{ route('currencies.bulk-delete') }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="currencies-table">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Currency</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Offcanvas Add / Edit --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCurrencyForm" style="width:420px">
        <div class="offcanvas-header border-bottom py-3">
            <h5 class="offcanvas-title d-flex align-items-center" id="form-title">
                <span class="avatar avatar-sm bg-label-primary me-2"><i class="ti tabler-coin"></i></span>
                Add Currency
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body pt-4">
            <form method="POST" action="{{ route('currencies.store') }}" id="currency-form">
                @csrf
                <input type="hidden" name="currency_id" id="currency_id">

                <div class="offcanvas-form-section">
                    <label class="form-label fw-semibold">Currency Details</label>

                    <div class="mb-3">
                        <label class="form-label small">Currency Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-hash"></i></span>
                            <input type="text" name="code" id="code" class="form-control text-uppercase" placeholder="e.g. EUR, GBP, JPY" required maxlength="10">
                        </div>
                        <small class="text-muted">
                            ISO 4217 codes —
                            <a href="https://gist.github.com/ksafranski/2973986#file-common-currency-json" target="_blank" rel="noopener noreferrer">
                                reference list <i class="ti tabler-external-link" style="font-size:.7rem"></i>
                            </a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Currency Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-typography"></i></span>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Euro, British Pound" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Symbol</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-currency-dollar"></i></span>
                            <input type="text" name="symbol" id="symbol" class="form-control" placeholder="e.g. €, £, ¥" maxlength="10">
                        </div>
                    </div>
                </div>

                <div class="offcanvas-form-section">
                    <label class="form-label fw-semibold">Settings</label>

                    <div class="mb-3">
                        <label class="form-label small">Set as Default</label>
                        <select name="is_default" id="is_default" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes — all prices displayed in this currency</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Status</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ti tabler-device-floppy me-1"></i> Save Currency
                    </button>
                    <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script>
$(function () {
    const csrfToken = '{{ csrf_token() }}';

    // ---------- DataTable ----------
    let table = $('#currencies-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("currencies.index") }}',
            dataSrc: function (json) {
                updateStats(json);
                return json.data;
            }
        },
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'code_col', name: 'code' },
            { data: 'symbol_col', orderable: false, searchable: false, className: 'text-center' },
            { data: 'rate_col', name: 'rate' },
            { data: 'default_badge', orderable: false, searchable: false, className: 'text-center' },
            { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            emptyTable: '<div class="text-center py-4"><i class="ti tabler-coin-off d-block mb-2" style="font-size:2rem;opacity:.4"></i>No currencies found</div>',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>'
        }
    });

    // ---------- Stats ----------
    function updateStats(json) {
        if (!json.data) return;
        let total = json.recordsTotal || 0;
        let active = 0, defaultCode = '—', lastSync = 'Never';

        json.data.forEach(function (row) {
            if (row.is_active) active++;
            if (row.is_default) defaultCode = row.code;
            if (row.fetched_at) {
                lastSync = row.fetched_at;
            }
        });

        $('#stat-total').text(total);
        $('#stat-active').text(active);
        $('#stat-default').text(defaultCode);
        if (lastSync !== 'Never') {
            let d = new Date(lastSync);
            $('#stat-last-sync').text(d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}));
        }
    }

    // ---------- Select All ----------
    $('#select-all').on('change', function () {
        $('.bulk-checkbox').prop('checked', this.checked);
        toggleBulkBtn();
    });

    $(document).on('change', '.bulk-checkbox', function () {
        toggleBulkBtn();
        let all = $('.bulk-checkbox').length;
        let checked = $('.bulk-checkbox:checked').length;
        $('#select-all').prop('checked', all === checked);
    });

    function toggleBulkBtn() {
        let count = $('.bulk-checkbox:checked').length;
        $('#selected-count').text(count);
        if (count > 0) {
            $('#bulk-bar').removeClass('d-none');
        } else {
            $('#bulk-bar').addClass('d-none');
        }
    }

    // ---------- Offcanvas Add / Edit ----------
    $('#offcanvasCurrencyForm').on('show.bs.offcanvas', function (e) {
        let btn = $(e.relatedTarget);
        let form = $('#currency-form');

        form.find('input[name="_method"]').remove();

        if (btn.data('edit')) {
            $('#form-title').html('<span class="avatar avatar-sm bg-label-warning me-2"><i class="ti tabler-pencil"></i></span> Edit Currency');
            form.attr('action', btn.data('url'));
            form.append('<input type="hidden" name="_method" value="PUT">');

            $('#currency_id').val(btn.data('id'));
            $('#code').val(btn.data('code'));
            $('#name').val(btn.data('name'));
            $('#symbol').val(btn.data('symbol'));
            $('#is_default').val(btn.data('is_default') ? 1 : 0);
            $('#is_active').val(btn.data('is_active') ? 1 : 0);
        } else {
            $('#form-title').html('<span class="avatar avatar-sm bg-label-primary me-2"><i class="ti tabler-coin"></i></span> Add Currency');
            form.attr('action', '{{ route("currencies.store") }}');
            form[0].reset();
            $('#currency_id').val('');
        }
    });

    // ---------- Delete ----------
    $(document).on('click', '.delete-btn', function () {
        let url = $(this).data('url');
        Swal.fire({
            title: 'Delete Currency?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: csrfToken },
                    success: function (res) {
                        Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                });
            }
        });
    });

    // ---------- Bulk Delete ----------
    $('#bulk-delete-btn').on('click', function () {
        let ids = [];
        $('.bulk-checkbox:checked').each(function () { ids.push($(this).val()); });

        if (!ids.length) return;

        Swal.fire({
            title: 'Delete ' + ids.length + ' currencies?',
            text: 'Default currencies will be skipped.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete all',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post($(this).data('url') || '{{ route("currencies.bulk-delete") }}', {
                    _token: csrfToken,
                    ids: ids
                }).done(function (res) {
                    Swal.fire({ icon: 'success', title: 'Deleted', text: res.message, timer: 1500, showConfirmButton: false });
                    table.ajax.reload(null, false);
                    $('#select-all').prop('checked', false);
                    toggleBulkBtn();
                }).fail(function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                });
            }
        });
    });

    // ---------- Update Rates ----------
    $('#update-rates-btn').on('click', function () {
        let btn = $(this);
        Swal.fire({
            title: 'Sync Exchange Rates?',
            text: 'This will fetch the latest rates from the API.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="ti tabler-refresh me-1"></i> Sync Now',
            customClass: { confirmButton: 'btn btn-warning me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                btn.prop('disabled', true);
                return $.post(btn.data('url'), { _token: csrfToken })
                    .then(function (res) { return res; })
                    .catch(function (xhr) {
                        Swal.showValidationMessage(xhr.responseJSON?.message || 'Failed to update rates.');
                    })
                    .always(function () { btn.prop('disabled', false); });
            },
            allowOutsideClick: function () { return !Swal.isLoading(); }
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                Swal.fire({ icon: 'success', title: 'Rates Synced', text: result.value.message, timer: 2500, showConfirmButton: false });
                table.ajax.reload(null, false);
            }
        });
    });

    // ---------- Set Default (AJAX) ----------
    $(document).on('click', '.set-default-btn', function () {
        let btn = $(this);
        Swal.fire({
            title: 'Set as Default?',
            text: '"' + btn.data('name') + '" will become the base currency.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, set default',
            customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: btn.data('url'),
                    type: 'PUT',
                    data: { _token: csrfToken, is_default: 1, code: btn.closest('.dropdown-menu').siblings('button').closest('tr').find('td:eq(1)').text().trim().split('\n')[0] || '', name: btn.data('name'), _quick: 1 },
                    success: function () {
                        Swal.fire({ icon: 'success', title: 'Default Updated', timer: 1200, showConfirmButton: false });
                        table.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
                    }
                });
            }
        });
    });

    // ---------- Toggle Status (AJAX) ----------
    $(document).on('click', '.toggle-status-btn', function () {
        let btn = $(this);
        let newStatus = btn.data('status');
        let label = newStatus ? 'Activate' : 'Deactivate';

        $.ajax({
            url: btn.data('url'),
            type: 'PUT',
            data: { _token: csrfToken, is_active: newStatus, _quick: 1 },
            success: function () {
                Swal.fire({ icon: 'success', title: label + 'd', timer: 1000, showConfirmButton: false });
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
            }
        });
    });
});
</script>
@endpush
