@extends('layouts.app')
@section('title', 'Sliders')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
.bulk-bar { background:linear-gradient(135deg,#f0f2ff 0%,#e8eaff 100%); border-radius:8px; border:1px solid rgba(115,103,240,.12); animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
.ctr-ring { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.7rem; }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-slideshow me-2"></i>Sliders</h4>
            <p class="text-muted mb-0">Manage homepage hero sliders, banners, and promotions</p>
        </div>
        <a href="{{ route('sliders.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> New Slider
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-slideshow fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Sliders</small>
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
                            <i class="ti tabler-player-play fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['live'] }}</h5>
                            <small class="text-muted">Live Now</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-info">
                            <i class="ti tabler-clock fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['scheduled'] }}</h5>
                            <small class="text-muted">Scheduled</small>
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
                            <i class="ti tabler-click fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ number_format($stats['views']) }}</h5>
                            <small class="text-muted">Total Views</small>
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
                <h5 class="mb-0"><i class="ti tabler-list-details me-2"></i>All Sliders</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-label-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filter-collapse">
                        <i class="ti tabler-filter me-1"></i> Filters
                    </button>
                </div>
            </div>

            <div class="collapse mt-3" id="filter-collapse">
                <div class="row g-3 pb-3 border-bottom">
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-layout ti-xs"></i></span>
                            <select class="form-select" id="filter-type">
                                <option value="">All Types</option>
                                <option value="hero">Hero</option>
                                <option value="banner">Banner</option>
                                <option value="promotional">Promotional</option>
                                <option value="product_spotlight">Product Spotlight</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-toggle-left ti-xs"></i></span>
                            <select class="form-select" id="filter-status">
                                <option value="">All Statuses</option>
                                <option value="live">Live</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="expired">Expired</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-fill" id="btn-apply">
                            <i class="ti tabler-search me-1"></i> Apply
                        </button>
                        <button class="btn btn-outline-secondary btn-sm flex-fill" id="btn-clear">
                            <i class="ti tabler-filter-off me-1"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">sliders selected</span>
                    </div>
                    <button class="btn btn-sm btn-label-danger" id="bulk-delete-btn">
                        <i class="ti tabler-trash me-1"></i> Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="sliders-table" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Slider</th>
                        <th>Product</th>
                        <th class="text-center">Schedule</th>
                        <th class="text-center">Views</th>
                        <th class="text-center">Position</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="120">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@push('page-js')
<script>
$(function () {
    const csrf = '{{ csrf_token() }}';

    const table = $('#sliders-table').DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: '{{ route("sliders.index") }}',
            data: d => {
                d.type   = $('#filter-type').val();
                d.status = $('#filter-status').val();
            }
        },
        order: [[5, 'asc']],
        columns: [
            { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
            { data: 'slider_col', name: 'title' },
            { data: 'product_col', orderable: false, searchable: false },
            { data: 'schedule_col', orderable: false, searchable: false, className: 'text-center' },
            { data: 'stats_col', name: 'views', className: 'text-center' },
            { data: 'position', name: 'position', className: 'text-center' },
            { data: 'status_col', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        drawCallback: () => syncBulk(),
        language: {
            emptyTable: '<div class="py-4 text-center"><i class="ti tabler-slideshow ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No sliders found</span></div>'
        }
    });

    $('#btn-apply').on('click', () => table.ajax.reload());
    $('#btn-clear').on('click', () => { $('#filter-type, #filter-status').val(''); table.ajax.reload(); });

    $('#select-all').on('change', function () { $('.bulk-checkbox').prop('checked', this.checked); syncBulk(); });
    $(document).on('change', '.bulk-checkbox', function () {
        $('#select-all').prop('checked', $('.bulk-checkbox:checked').length === $('.bulk-checkbox').length);
        syncBulk();
    });

    function syncBulk() {
        const c = $('.bulk-checkbox:checked').length;
        c > 0 ? $('#bulk-bar').removeClass('d-none').find('#bulk-count').text(c) : $('#bulk-bar').addClass('d-none');
    }

    $(document).on('click', '.toggle-btn', function () {
        const id = $(this).data('id');
        $.post('/sliders/' + id + '/toggle', { _token: csrf }, () => {
            table.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Status toggled', timer: 1000, showConfirmButton: false });
        });
    });

    $(document).on('click', '.delete-btn', function () {
        const url = $(this).data('url');
        Swal.fire({
            title: 'Delete slider?', text: 'This cannot be undone.', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({ url, type: 'DELETE', data: { _token: csrf },
                success: () => { table.ajax.reload(null, false); Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false }); }
            });
        });
    });

    $('#bulk-delete-btn').on('click', function () {
        const ids = $('.bulk-checkbox:checked').map((_, el) => el.value).get();
        if (!ids.length) return;
        Swal.fire({
            title: 'Delete ' + ids.length + ' slider(s)?', icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Delete All',
            customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({ url: '{{ route("sliders.bulk-delete") }}', type: 'DELETE', data: { ids, _token: csrf },
                success: () => {
                    table.ajax.reload(null, false); $('#select-all').prop('checked', false); syncBulk();
                    Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
                }
            });
        });
    });
});
</script>
@endpush
