@extends('layouts.app')
@section('title','My Wallet')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Wallet History</h5>
        <strong>Balance: {{ number_format(auth()->user()->wallet->balance,2) }}</strong>
    </div>

    <div class="table-responsive">
        <table class="table" id="wallet-history">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Source</th>
                    <th>Date</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('page-js')
<script>
$('#wallet-history').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('wallet.history') }}',
    columns: [
        { data: 'type' },
        { data: 'amount' },
        { data: 'source' },
        { data: 'created_at' }
    ]
});
</script>
@endpush
