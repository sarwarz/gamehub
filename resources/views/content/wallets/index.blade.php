@extends('layouts.app')
@section('title','Wallets')

@section('content')
<div class="app-ecommerce">

<div class="card p-2">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Wallets</h5>
        <button class="btn btn-success" id="credit-debit-wallet">
            Credit/Debit Wallet
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="wallets-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

</div>
@endsection

@push('page-js')
<script>
let table = $('#wallets-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('wallets.index') }}',
    columns: [
        { data: 'user', orderable: false },
        { data: 'balance', name: 'balance' },
        { data: 'status', orderable: false },
        { data: 'actions', orderable: false }
    ]
});

/* ===========================
   CREDIT / DEBIT POPUP
=========================== */
$('#credit-debit-wallet').on('click', function () {
    Swal.fire({
        title: 'Credit / Debit Wallet',
        html: `
            <select id="wallet_type" class="form-control mb-3">
                <option value="">Select Type</option>
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
            </select>

            <select id="user_id" class="select2 form-control mb-3 ">
                <option value="">Select User</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>

            <input id="amount" type="number" step="0.01"
                   class="form-control mb-3"
                   placeholder="Amount">

            <input id="description"
                   class="form-control mb-3"
                   placeholder="Description (optional)">
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit',
        focusConfirm: false,

        didOpen: () => {
            $('#user_id').select2({
                dropdownParent: Swal.getPopup(),
                width: '100%',
                placeholder: 'Select User'
            });
        },
        
        preConfirm: () => {
            return {
                type: document.getElementById('wallet_type').value,
                user_id: document.getElementById('user_id').value,
                amount: document.getElementById('amount').value,
                description: document.getElementById('description').value
            };
        }
    }).then((result) => {

        if (!result.isConfirmed) return;

        let data = result.value;

        if (!data.type || !data.user_id || !data.amount) {
            Swal.fire('Error', 'Type, User, and Amount are required', 'error');
            return;
        }

        let url = `/dashboard/wallet/${data.user_id}/${data.type}`;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                amount: data.amount,
                description: data.description
            },
            success: function () {
                Swal.fire(
                    'Success',
                    `Wallet ${data.type}ed successfully`,
                    'success'
                );
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire(
                    'Error',
                    xhr.responseJSON?.message ?? 'Operation failed',
                    'error'
                );
            }
        });
    });
});
</script>
@endpush

