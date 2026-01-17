@extends('layouts.app')
@section('title', 'Coupons')

@section('content')
<div class="card p-2">
    <div class="card-header d-flex justify-content-between">
        <h5>Coupons</h5>
        <a href="{{ route('coupons.create') }}" class="btn btn-primary">Add Coupon</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="coupon-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Used</th>
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
$('#coupon-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('coupons.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable:false, searchable:false },
        { data: 'code', name:'code' },
        { data: 'discount', orderable:false },
        { data: 'used', name:'used' },
        { data: 'status', orderable:false },
        { data: 'actions', orderable:false }
    ]
});
</script>
@endpush
