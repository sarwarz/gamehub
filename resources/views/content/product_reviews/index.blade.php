@extends('layouts.app')
@section('title', 'Product Reviews')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-products">

    @include('partials.alerts')

    <!-- Product Reviews Table -->
    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Reviews</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="product-reviews-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Review</th>
                        <th>IP Address</th>
                        <th>Verified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
<!-- View Review Modal -->
<div class="modal fade" id="viewReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <table class="table table-sm table-bordered">
                    <tr><th>Product</th><td id="vr-product"></td></tr>
                    <tr><th>User</th><td id="vr-user"></td></tr>
                    <tr><th>Email</th><td id="vr-email"></td></tr>
                    <tr><th>Rating</th><td id="vr-rating"></td></tr>
                    <tr><th>Title</th><td id="vr-title"></td></tr>
                    <tr><th>Review</th><td id="vr-review"></td></tr>
                    <tr><th>Status</th><td id="vr-status"></td></tr>
                    <tr><th>Verified</th><td id="vr-verified"></td></tr>
                    <tr><th>IP Address</th><td id="vr-ip"></td></tr>
                    <tr><th>User Agent</th><td id="vr-agent"></td></tr>
                    <tr><th>Created At</th><td id="vr-date"></td></tr>
                </table>

            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
let table = $('#product-reviews-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('product-reviews.index') }}',
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'review_info', name: 'review_info', orderable: false, searchable: true },
        { data: 'ip_address', orderable: false, searchable: false },
        { data: 'verified', name: 'verified', orderable: false, searchable: false },
        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
        { data: 'actions', name: 'actions', orderable: false, searchable: false }
    ]
});

/* ===== View Review ===== */
$(document).on('click', '.btn-view', function () {
    let url = $(this).data('url');

    $.get(url, function (res) {
        $('#vr-product').text(res.product);
        $('#vr-user').text(res.user);
        $('#vr-email').text(res.email);
        $('#vr-rating').html('⭐'.repeat(res.rating));
        $('#vr-title').text(res.title ?? '-');
        $('#vr-review').text(res.review ?? '-');
        $('#vr-status').text(res.status);
        $('#vr-verified').text(res.verified ? 'Yes' : 'No');
        $('#vr-ip').text(res.ip ?? '-');
        $('#vr-agent').text(res.agent ?? '-');
        $('#vr-date').text(res.created_at);

        $('#viewReviewModal').modal('show');
    });
});


/* ===== Approve Review ===== */
$(document).on('click', '.btn-approve', function () {
    let url = $(this).data('url');

    Swal.fire({
        title: 'Approve this review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve',
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(url, {_token: '{{ csrf_token() }}'}, function () {
                table.ajax.reload(null, false);
                Swal.fire('Approved!', 'Review approved successfully.', 'success');
            });
        }
    });
});

/* ===== Reject Review ===== */
$(document).on('click', '.btn-reject', function () {
    let url = $(this).data('url');

    Swal.fire({
        title: 'Reject this review?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reject',
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(url, {_token: '{{ csrf_token() }}'}, function () {
                table.ajax.reload(null, false);
                Swal.fire('Rejected!', 'Review rejected successfully.', 'success');
            });
        }
    });
});

/* ===== Delete Review ===== */
$(document).on('click', '.btn-delete', function () {
    let url = $(this).data('url');

    Swal.fire({
        title: 'Delete this review?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {_token: '{{ csrf_token() }}'},
                success: function () {
                    table.ajax.reload(null, false);
                    Swal.fire('Deleted!', 'Review deleted successfully.', 'success');
                }
            });
        }
    });
});
</script>
@endpush
