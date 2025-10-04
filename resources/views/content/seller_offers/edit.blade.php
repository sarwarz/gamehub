@extends('layouts.app')
@section('title', 'Edit Seller Offer')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce">
    @include('partials.alerts')
    <form method="POST" action="{{ route('seller-offers.update', $offer->id) }}">
        @csrf
        @method('PUT')

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Edit Offer</h4>
            <a href="{{ route('seller-offers.index') }}" class="btn btn-label-secondary">Back</a>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-12 col-lg-8">
                <!-- Product Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Product</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Product</label>
                            <select name="product_id" class="form-select select2 @error('product_id') is-invalid @enderror" required>
                                @foreach(\App\Models\Product::active()->get() as $product)
                                    <option value="{{ $product->id }}" {{ $offer->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Seller</label>
                            <select name="seller_id" class="form-select select2 @error('seller_id') is-invalid @enderror" required>
                                @foreach(\App\Models\Seller::all() as $seller)
                                    <option value="{{ $seller->id }}" {{ $offer->seller_id == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->store_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seller_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Preview -->
                <div id="product-preview" class="card mt-2 mb-4">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <img id="preview-cover" src="{{ $offer->product->cover_image ? asset('storage/'.$offer->product->cover_image) : asset('assets/img/default-product.png') }}" 
                                 alt="Cover" class="rounded" width="120">
                        </div>
                        <div>
                            <h6 id="preview-title" class="mb-2">{{ $offer->product->title }}</h6>
                            <p class="mb-1"><strong>Type:</strong> {{ optional($offer->product->types->first())->name ?? '-' }}</p>
                            <p class="mb-1"><strong>Region:</strong> {{ $offer->product->regions->pluck('name')->join(', ') }}</p>
                            <p class="mb-1"><strong>Language:</strong> {{ $offer->product->languages->pluck('name')->join(', ') }}</p>
                            <p class="mb-0"><strong>Platform:</strong> {{ $offer->product->platforms->pluck('name')->join(', ') }}</p>
                        </div>
                    </div>
                </div>

                @php
                    $currencyService = app(\App\Services\CurrencyService::class);
                    $currencyCode = $currencyService->code();   // e.g. "USD"
                    $currencySymbol = $currencyService->symbol(); // e.g. "$"
                @endphp

                <!-- Retail Pricing -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Retail Pricing</h5></div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Final Price ({{ $currencyCode }})</label>
                                <input type="number" step="0.01" name="retail_price" id="retail_price"
                                    class="form-control" value="{{ old('retail_price', $offer->retail_price) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Profit</label>
                                <input type="text" id="retail_profit" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Acquisition Cost</label>
                                <input type="number" step="0.01" name="retail_acquisition_cost" id="retail_acquisition_cost"
                                    class="form-control" value="{{ old('retail_acquisition_cost', $offer->retail_acquisition_cost) }}">
                            </div>
                        </div>
                        <p class="text-muted small">
                            Commission: <span id="retail_commission">0</span> {{ $currencyCode }} <br>
                            Seller share: <span class="text-success"><span id="retail_share">0</span> {{ $currencyCode }}</span>
                        </p>
                    </div>
                </div>

                <!-- Wholesale Pricing -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Wholesale Pricing</h5></div>
                    <div class="card-body">
                        <h6 class="mb-3">10–99 Keys</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="wholesale_10_99_price" id="wholesale_10_99_price"
                                    class="form-control" value="{{ old('wholesale_10_99_price', $offer->wholesale_10_99_price) }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="wholesale_10_99_profit" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="wholesale_10_99_acquisition_cost" id="wholesale_10_99_acquisition_cost"
                                    class="form-control" value="{{ old('wholesale_10_99_acquisition_cost', $offer->wholesale_10_99_acquisition_cost) }}">
                            </div>
                        </div>
                        <p class="text-muted small">
                            Commission: <span id="wholesale_10_99_commission">0</span> {{ $currencyCode }} <br>
                            Seller share: <span class="text-success"><span id="wholesale_10_99_share">0</span> {{ $currencyCode }}</span>
                        </p>

                        <h6 class="mb-3">100+ Keys</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="wholesale_100_plus_price" id="wholesale_100_price"
                                    class="form-control" value="{{ old('wholesale_100_plus_price', $offer->wholesale_100_plus_price) }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="wholesale_100_profit" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="wholesale_100_acquisition_cost" id="wholesale_100_acquisition_cost"
                                    class="form-control" value="{{ old('wholesale_100_acquisition_cost', $offer->wholesale_100_acquisition_cost) }}">
                            </div>
                        </div>
                        <p class="text-muted small">
                            Commission: <span id="wholesale_100_commission">0</span> {{ $currencyCode }} <br>
                            Seller share: <span class="text-success"><span id="wholesale_100_share">0</span> {{ $currencyCode }}</span>
                        </p>
                    </div>
                </div>

                <!-- Keys -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Keys</h5></div>
                    <div class="card-body">
                        <textarea name="keys_text" rows="5" class="form-control @error('keys_text') is-invalid @enderror"
                            placeholder="Enter one code per line">{{ old('keys_text', $offer->keys->pluck('value')->join("\n")) }}</textarea>
                        @error('keys_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-12 col-lg-4">
                <!-- Settings -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Offer Settings</h5></div>
                    <div class="card-body">
                        <select name="sale_mode" class="form-select mb-3">
                            <option value="retail" {{ $offer->sale_mode == 'retail' ? 'selected' : '' }}>Retail Only</option>
                            <option value="wholesale" {{ $offer->sale_mode == 'wholesale' ? 'selected' : '' }}>Wholesale Only</option>
                            <option value="both" {{ $offer->sale_mode == 'both' ? 'selected' : '' }}>Both</option>
                        </select>

                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" name="is_verified" value="1" {{ $offer->is_verified ? 'checked' : '' }}>
                            <label class="form-check-label">Verified Offer</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" name="is_promoted" value="1" {{ $offer->is_promoted ? 'checked' : '' }}>
                            <label class="form-check-label">Promoted Offer</label>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Offer Status</h5></div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            <option value="draft" {{ $offer->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ $offer->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $offer->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Update Offer</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('page-js')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
    $(function() {
        $('.select2').select2({
            placeholder: "-- Select Option --",
            allowClear: true,
            width: '100%'
        });
    });
</script>

<script>
$(function () {
    $('select[name="product_id"]').on('change', function () {
        let productId = $(this).val();
        if (!productId) {
            $('#product-preview').addClass('d-none');
            return;
        }

        $.get("{{ url('dashboard/products') }}/" + productId + "/preview", function (data) {
            $('#preview-cover').attr('src', data.cover);
            $('#preview-title').text(data.title);

            $('#preview-type').text(data.types.length ? data.types.join(', ') : '-');
            $('#preview-region').text(data.regions.length ? data.regions.join(', ') : '-');
            $('#preview-language').text(data.languages.length ? data.languages.join(', ') : '-');
            $('#preview-platform').text(data.platforms.length ? data.platforms.join(', ') : '-');

            $('#product-preview').removeClass('d-none');
        });
    });
});
</script>

<script>
$(document).ready(function () {
    // Commission rate global
    let COMMISSION_RATE = 0;

    // Function to calculate pricing
    function calculatePricing(priceInput, costInput, commissionSpan, shareSpan, profitInput) {
        let price = parseFloat(priceInput.val()) || 0;
        let cost = parseFloat(costInput.val()) || 0;
        let commission = (price * COMMISSION_RATE / 100).toFixed(2);
        let share = (price - commission).toFixed(2);
        let profit = (share - cost).toFixed(2);

        commissionSpan.text(commission);
        shareSpan.text(share);
        profitInput.val(profit);
    }

    // Fetch product preview + commission when product changes
    $('select[name="product_id"]').on('change', function () {
        let productId = $(this).val();
        if (!productId) {
            $('#product-preview').addClass('d-none');
            COMMISSION_RATE = 0;
            return;
        }

        $.get("{{ url('dashboard/products') }}/" + productId + "/preview", function (data) {
            $('#preview-cover').attr('src', data.cover);
            $('#preview-title').text(data.title);

            $('#preview-type').text(data.types.length ? data.types.join(', ') : '-');
            $('#preview-region').text(data.regions.length ? data.regions.join(', ') : '-');
            $('#preview-language').text(data.languages.length ? data.languages.join(', ') : '-');
            $('#preview-platform').text(data.platforms.length ? data.platforms.join(', ') : '-');

            //  set commission dynamically
            COMMISSION_RATE = data.commission || 0;

            $('#product-preview').removeClass('d-none');

            // Recalculate all existing inputs
            $('#retail_price, #retail_acquisition_cost').trigger('input');
            $('#wholesale_10_99_price, #wholesale_10_99_acquisition_cost').trigger('input');
            $('#wholesale_100_price, #wholesale_100_acquisition_cost').trigger('input');
        });
    });

    // Attach input handlers
    $('#retail_price, #retail_acquisition_cost').on('input', function () {
        calculatePricing($('#retail_price'), $('#retail_acquisition_cost'),
            $('#retail_commission'), $('#retail_share'), $('#retail_profit'));
    });

    $('#wholesale_10_99_price, #wholesale_10_99_acquisition_cost').on('input', function () {
        calculatePricing($('#wholesale_10_99_price'), $('#wholesale_10_99_acquisition_cost'),
            $('#wholesale_10_99_commission'), $('#wholesale_10_99_share'), $('#wholesale_10_99_profit'));
    });

    $('#wholesale_100_price, #wholesale_100_acquisition_cost').on('input', function () {
        calculatePricing($('#wholesale_100_price'), $('#wholesale_100_acquisition_cost'),
            $('#wholesale_100_commission'), $('#wholesale_100_share'), $('#wholesale_100_profit'));
    });

    //  Initialize preview & commission if editing existing offer
    let selectedProduct = $('select[name="product_id"]').val();
    if (selectedProduct) {
        $('select[name="product_id"]').trigger('change');
    }
});

</script>
@endpush
