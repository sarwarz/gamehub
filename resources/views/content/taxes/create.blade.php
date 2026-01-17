@extends('layouts.app')
@section('title', 'Create Tax')

@section('content')
<form method="POST" action="{{ route('taxes.store') }}">
@csrf

<div class="card p-4">
    <h5>Create Tax Rule</h5>

    <div class="row mt-3">
        <div class="col-md-6">
            <label class="form-label">Tax Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Seller (Optional)</label>
            <select name="seller_id" class="form-select">
                <option value="">Global</option>
                @foreach($sellers as $seller)
                    <option value="{{ $seller->id }}">{{ $seller->store_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Country</label>
            <input type="text" name="country" class="form-control" placeholder="US">
        </div>
        <div class="col-md-4">
            <label>State</label>
            <input type="text" name="state" class="form-control">
        </div>
        <div class="col-md-4">
            <label>City</label>
            <input type="text" name="city" class="form-control">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <label>Type</label>
            <select name="type" class="form-select">
                <option value="percent">Percent</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>

        <div class="col-md-4">
            <label>Rate</label>
            <input type="number" step="0.0001" name="rate" class="form-control" required>
        </div>

        <div class="col-md-4 d-flex align-items-center mt-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" checked>
                <label class="form-check-label">Active</label>
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <button class="btn btn-primary">Save Tax</button>
    </div>
</div>
</form>
@endsection
