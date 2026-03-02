@extends('layouts.app')
@section('title', 'Tax Rules')

@section('content')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-receipt-tax me-2"></i>Tax Rules</h4>
            <p class="text-muted mb-0">Manage tax rates for different regions and sellers</p>
        </div>
        <a href="{{ route('taxes.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Add Tax Rule
        </a>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-label-primary">
                            <i class="ti tabler-receipt-tax fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small class="text-muted">Total Tax Rules</small>
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
                            <h5 class="mb-0">{{ $stats['active'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable Card --}}
    <div class="card">
        <div class="card-header pb-0">
            <h5 class="mb-0"><i class="ti tabler-list me-2"></i>All Tax Rules</h5>
        </div>

        {{-- Bulk Actions Bar --}}
        <div class="card-body py-0">
            <div class="bulk-bar d-none py-2" id="bulk-bar">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6" id="bulk-count">0</span>
                        <span class="fw-medium" style="font-size:.85rem">tax rules selected</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-label-danger bulk-delete-btn" type="button">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table id="taxes-table" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40"><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>#</th>
                        <th>Name</th>
                        <th>Scope</th>
                        <th>Location</th>
                        <th class="text-end">Rate</th>
                        <th>Flags</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="80">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('page-css')
<style>
.bulk-bar { background:#f0f2ff; border-radius:8px; animation:bulkSlide .3s ease; }
@keyframes bulkSlide { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@push('page-js')
<script>
$(function() {
    const table = $('#taxes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("taxes.index") }}',
        order: [[2, 'asc']],
        pageLength: 25,
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name_col', name: 'name' },
            { data: 'scope', orderable: false },
            { data: 'location', orderable: false },
            { data: 'rate_display', orderable: false, className: 'text-end' },
            { data: 'flags', orderable: false },
            { data: 'status', orderable: false, className: 'text-center' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            emptyTable: '<div class="py-4 text-center"><i class="ti tabler-receipt-off ti-xl text-muted mb-2 d-block"></i><span class="text-muted">No tax rules found</span></div>'
        }
    });

    $('#select-all').on('change', function() {
        $('.bulk-checkbox').prop('checked', this.checked);
        updateBulkBar();
    });

    $(document).on('change', '.bulk-checkbox', function() {
        updateBulkBar();
    });

    function getSelectedIds() {
        return $('.bulk-checkbox:checked').map(function() { return $(this).val(); }).get();
    }

    function updateBulkBar() {
        const ids = getSelectedIds();
        if (ids.length > 0) {
            $('#bulk-bar').removeClass('d-none');
            $('#bulk-count').text(ids.length);
        } else {
            $('#bulk-bar').addClass('d-none');
        }
        $('#select-all').prop('checked', $('.bulk-checkbox').length > 0 && $('.bulk-checkbox:not(:checked)').length === 0);
    }

    $(document).on('click', '.bulk-delete-btn', function() {
        const ids = getSelectedIds();
        if (!ids.length) return;

        Swal.fire({
            title: 'Delete Selected Tax Rules?',
            text: ids.length + ' tax rule(s) will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '{{ route("taxes.bulk-delete") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function() {
                    table.ajax.reload(null, false);
                    $('#bulk-bar').addClass('d-none');
                    Swal.fire({ icon: 'success', title: 'Tax rules deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Failed to delete', showConfirmButton: false, timer: 1500 });
                }
            });
        });
    });

    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        Swal.fire({
            title: 'Delete Tax Rule?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(r => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Tax rule deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Failed', showConfirmButton: false, timer: 1500 });
                }
            });
        });
    });
});
</script>
@endpush
