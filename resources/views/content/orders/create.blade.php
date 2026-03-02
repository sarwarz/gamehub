@extends('layouts.app')
@section('title', 'Create Order')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<style>
    .order-summary-card { position: sticky; top: 1.5rem; }
    .product-row:not(:last-child) { border-bottom: 1px solid var(--bs-border-color); }
    .offer-price-tag { font-size: 0.7rem; letter-spacing: .5px; }
</style>
@endpush

@section('content')
<div class="app-ecommerce-order-create">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-file-plus ti-md me-1 text-primary"></i> Create New Order</h4>
            <p class="text-muted mb-0">Manually create an order on behalf of a customer</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-label-secondary">
            <i class="ti tabler-arrow-left ti-xs me-1"></i> Back to Orders
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <div class="d-flex align-items-center">
            <i class="ti tabler-alert-circle ti-md me-2"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST" id="createOrderForm">
        @csrf
        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Customer Card --}}
                <div class="card mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-user ti-md me-2 text-primary"></i> Customer</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                <select name="buyer_id" id="buyer_id" class="form-select select2 @error('buyer_id') is-invalid @enderror" required>
                                    <option value="">-- Search or select a customer --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                                data-name="{{ $user->name }}"
                                                data-email="{{ $user->email }}"
                                                {{ old('buyer_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('buyer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Products Card --}}
                <div class="card mb-4">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="ti tabler-package ti-md me-2 text-primary"></i> Order Items</h5>
                        <button type="button" id="add-row" class="btn btn-sm btn-primary">
                            <i class="ti tabler-plus ti-xs me-1"></i> Add Product
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="products-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:200px">Product</th>
                                        <th style="min-width:200px">Seller Offer</th>
                                        <th class="text-center" width="100">Qty</th>
                                        <th class="text-end" width="130">Subtotal</th>
                                        <th class="text-center" width="60"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php $oldItems = old('items', [['quantity' => 1]]); @endphp
                                @foreach($oldItems as $index => $oldItem)
                                    <tr class="product-row">
                                        <td>
                                            <select class="form-select form-select-sm product-select">
                                                <option value="">-- Select Product --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm offer-select"
                                                    name="items[{{ $index }}][seller_offer_id]"
                                                    data-old="{{ $oldItem['seller_offer_id'] ?? '' }}"
                                                    disabled required>
                                                <option value="">-- Select Offer --</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]"
                                                   class="form-control form-control-sm qty text-center"
                                                   value="{{ $oldItem['quantity'] ?? 1 }}" min="1">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm subtotal text-end fw-semibold" value="0.00" readonly>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-icon btn-text-danger btn-remove">
                                                <i class="ti tabler-trash ti-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 bg-lighter text-muted small" id="no-items-msg" style="{{ count($oldItems) ? 'display:none' : '' }}">
                            <i class="ti tabler-info-circle ti-xs me-1"></i> Add at least one product to the order.
                        </div>
                    </div>
                </div>

                {{-- Billing Address Card --}}
                <div class="card mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-map-pin ti-md me-2 text-primary"></i> Billing Address</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-user ti-xs"></i></span>
                                    <input type="text" class="form-control @error('billing.name') is-invalid @enderror"
                                           id="billing_name" name="billing[name]" value="{{ old('billing.name') }}" placeholder="John Doe" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-mail ti-xs"></i></span>
                                    <input type="email" class="form-control @error('billing.email') is-invalid @enderror"
                                           id="billing_email" name="billing[email]" value="{{ old('billing.email') }}" placeholder="john@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-phone ti-xs"></i></span>
                                    <input type="text" class="form-control" name="billing[phone]" value="{{ old('billing.phone') }}" placeholder="+1 234 567 890">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-world ti-xs"></i></span>
                                    <input type="text" class="form-control @error('billing.country') is-invalid @enderror"
                                           name="billing[country]" value="{{ old('billing.country') }}" placeholder="United States" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Street Address <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti tabler-home ti-xs"></i></span>
                                    <input type="text" class="form-control @error('billing.address') is-invalid @enderror"
                                           name="billing[address]" value="{{ old('billing.address') }}" placeholder="123 Main St" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('billing.city') is-invalid @enderror"
                                       name="billing[city]" value="{{ old('billing.city') }}" placeholder="New York" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" class="form-control" name="billing[state]" value="{{ old('billing.state') }}" placeholder="NY">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="billing[postcode]" value="{{ old('billing.postcode') }}" placeholder="10001">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column (Sticky Summary) --}}
            <div class="col-xl-4 col-lg-5">

                {{-- Payment Card --}}
                <div class="card mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0"><i class="ti tabler-credit-card ti-md me-2 text-primary"></i> Payment</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->name }}" {{ old('payment_method') == $method->name ? 'selected' : '' }}>
                                        {{ $method->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Reference</label>
                            <input type="text" class="form-control" name="payment_ref" value="{{ old('payment_ref') }}" placeholder="Transaction ID or ref #">
                        </div>
                        <div>
                            <label class="form-label">Order Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach(['pending','processing','completed','cancelled'] as $status)
                                    <option value="{{ $status }}" {{ old('status','pending') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Order Summary Card --}}
                <div class="card mb-4 order-summary-card">
                    <div class="card-header border-bottom bg-label-primary">
                        <h5 class="card-title mb-0 text-primary"><i class="ti tabler-receipt ti-md me-2"></i> Order Summary</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold" id="order-subtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Fees / Adjustments</span>
                            <input type="number" step="0.01" name="fees" id="fees"
                                   value="{{ old('fees', 0) }}"
                                   class="form-control form-control-sm text-end" style="width: 100px;">
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary" id="order-total">$0.00</span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent pt-0">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="ti tabler-check ti-xs me-1"></i> Create Order
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('page-js')
<script>
(function($) {
    'use strict';

    let rowIndex = {{ count(old('items', [['quantity'=>1]])) }};
    const currencySymbol = '{{ app(\App\Services\CurrencyService::class)->symbol() }}';

    function formatMoney(val) {
        return currencySymbol + parseFloat(val || 0).toFixed(2);
    }

    $('#add-row').on('click', function () {
        const row = `
        <tr class="product-row">
            <td>
                <select class="form-select form-select-sm product-select">
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm offer-select" name="items[${rowIndex}][seller_offer_id]" disabled required>
                    <option value="">-- Select Offer --</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty text-center" value="1" min="1">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm subtotal text-end fw-semibold" value="0.00" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-icon btn-text-danger btn-remove">
                    <i class="ti tabler-trash ti-sm"></i>
                </button>
            </td>
        </tr>`;
        $('#products-table tbody').append(row);
        $('#no-items-msg').hide();
        rowIndex++;
    });

    $(document).on('click', '.btn-remove', function () {
        $(this).closest('tr').remove();
        calculateTotals();
        if ($('#products-table tbody tr').length === 0) {
            $('#no-items-msg').show();
        }
    });

    $(document).on('change', '.product-select', function () {
        const row = $(this).closest('tr');
        const offerSelect = row.find('.offer-select');
        const productId = $(this).val();

        offerSelect.prop('disabled', true).html('<option>Loading...</option>');

        if (productId) {
            $.get('/dashboard/products/' + productId + '/offers', function (data) {
                let options = '<option value="">-- Select Offer --</option>';
                data.forEach(offer => {
                    options += `<option value="${offer.id}" data-price="${offer.retail_price}">
                        ${offer.seller} — ${currencySymbol}${parseFloat(offer.retail_price).toFixed(2)}
                    </option>`;
                });
                offerSelect.html(options).prop('disabled', false);
            }).fail(function() {
                offerSelect.html('<option value="">No offers available</option>');
            });
        } else {
            offerSelect.html('<option value="">-- Select Offer --</option>');
        }
    });

    $(document).on('change input', '.offer-select, .qty, #fees', function () {
        calculateTotals();
    });

    function calculateTotals() {
        let subtotal = 0;
        $('#products-table tbody tr').each(function () {
            const row = $(this);
            const price = parseFloat(row.find('.offer-select option:selected').data('price')) || 0;
            const qty = parseInt(row.find('.qty').val()) || 0;
            const line = price * qty;
            row.find('.subtotal').val(line.toFixed(2));
            subtotal += line;
        });

        $('#order-subtotal').text(formatMoney(subtotal));
        const fees = parseFloat($('#fees').val()) || 0;
        $('#order-total').text(formatMoney(subtotal + fees));
    }

    $(document).ready(function () {
        $('#buyer_id').on('change', function () {
            const option = $(this).find(':selected');
            if (option.val()) {
                $('#billing_name').val(option.data('name') || '');
                $('#billing_email').val(option.data('email') || '');
            }
        });

        $('#products-table tbody tr').each(function () {
            const row = $(this);
            const productSelect = row.find('.product-select');
            const offerSelect = row.find('.offer-select');
            const oldOffer = offerSelect.data('old');

            if (productSelect.val()) {
                $.get('/dashboard/products/' + productSelect.val() + '/offers', function (data) {
                    let options = '<option value="">-- Select Offer --</option>';
                    data.forEach(offer => {
                        options += `<option value="${offer.id}" data-price="${offer.retail_price}"
                            ${offer.id == oldOffer ? 'selected' : ''}>
                            ${offer.seller} — ${currencySymbol}${parseFloat(offer.retail_price).toFixed(2)}
                        </option>`;
                    });
                    offerSelect.html(options).prop('disabled', false);
                    calculateTotals();
                });
            }
        });
    });

})(jQuery);
</script>
@endpush
