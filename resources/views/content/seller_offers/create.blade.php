@extends('layouts.app')
@section('title', 'Create Seller Offer')

@section('content')

    @include('partials.alerts')

    <form method="POST" action="{{ route('seller-offers.store') }}">
        @csrf

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1"><i class="ti tabler-tag me-2"></i>Create New Offer</h4>
                <p class="text-muted mb-0">Set up product pricing, keys, and offer details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('seller-offers.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Save Offer
                </button>
            </div>
        </div>

        @include('content.seller_offers._form')
    </form>

@endsection
