@extends('layouts.app')
@section('title', 'Taxes')

@section('content')
<div class="card p-2">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tax Rules</h5>
        <a href="{{ route('taxes.create') }}" class="btn btn-primary">
            Add Tax
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="taxes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Scope</th>
                    <th>Location</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('page-js')
<script>
$('#taxes-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('taxes.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false },
        { data: 'name' },
        { data: 'scope', orderable: false },
        { data: 'location', orderable: false },
        { data: 'rate_display', orderable: false },
        { data: 'status', orderable: false },
        { data: 'actions', orderable: false, searchable: false },
    ]
});
</script>
@endpush
