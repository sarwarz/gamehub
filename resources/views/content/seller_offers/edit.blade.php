@extends('layouts.app')
@section('title', 'Edit Seller Offer')

@section('content')

    @include('partials.alerts')

    <form method="POST" action="{{ route('seller-offers.update', $offer->id) }}">
        @csrf
        @method('PUT')

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1"><i class="ti tabler-pencil me-2"></i>Edit Offer</h4>
                <p class="text-muted mb-0">Update offer for <strong>{{ $offer->product?->title }}</strong></p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('seller-offers.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Update Offer
                </button>
            </div>
        </div>

        @include('content.seller_offers._form')
    </form>

@endsection
