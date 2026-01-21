@extends('layouts.app')
@section('title', 'Add Order')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-order-create">

    @include('partials.alerts')

    {{-- GLOBAL ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti tabler-shopping-cart"></i> Create New Order</h5>
            <a href="{{ route('orders.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST">
                @csrf

                {{-- CUSTOMER --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Customer</label>
                    <select name="buyer_id"
                            id="buyer_id"
                            class="form-select select2 @error('buyer_id') is-invalid @enderror"
                            required>
                        <option value="">-- Select Customer --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}"
                                    {{ old('buyer_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PRODUCTS --}}
                <div class="mb-4">
                    <label class="form-label fw-bold">Products & Offers</label>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Offer</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $oldItems = old('items', [['quantity' => 1]]);
                            @endphp

                            @foreach($oldItems as $index => $oldItem)
                                <tr>
                                    <td style="min-width:220px">
                                        <select class="form-select product-select">
                                            <option value="">-- Select Product --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td style="min-width:220px">
                                        <select class="form-select offer-select"
                                                name="items[{{ $index }}][seller_offer_id]"
                                                data-old="{{ $oldItem['seller_offer_id'] ?? '' }}"
                                                disabled required>
                                            <option value="">-- Select Offer --</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number"
                                               name="items[{{ $index }}][quantity]"
                                               class="form-control qty text-center"
                                               value="{{ $oldItem['quantity'] ?? 1 }}"
                                               min="1">
                                    </td>

                                    <td>
                                        <input type="text" class="form-control subtotal text-end" value="0.00" readonly>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                                            <i class="ti tabler-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-row" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ti tabler-plus"></i> Add Product
                    </button>
                </div>

                {{-- BILLING --}}
                <div class="mb-4">
                    <h6 class="fw-bold">Billing Address</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input class="form-control"
                                   id="billing_name"
                                   name="billing[name]"
                                   value="{{ old('billing.name') }}"
                                   placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control"
                                   id="billing_email"
                                   name="billing[email]"
                                   value="{{ old('billing.email') }}"
                                   placeholder="Email">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="billing[phone]" value="{{ old('billing.phone') }}" placeholder="Phone">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="billing[country]" value="{{ old('billing.country') }}" placeholder="Country" required>
                        </div>
                        <div class="col-12">
                            <input class="form-control" name="billing[address]" value="{{ old('billing.address') }}" placeholder="Address" required>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="billing[city]" value="{{ old('billing.city') }}" placeholder="City" required>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="billing[postcode]" value="{{ old('billing.postcode') }}" placeholder="Postcode">
                        </div>
                    </div>
                </div>

                {{-- PAYMENT --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <select name="payment_method" class="form-select">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->name }}"
                                    {{ old('payment_method') == $method->name ? 'selected' : '' }}>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" name="payment_ref" value="{{ old('payment_ref') }}" placeholder="Payment Reference">
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="mb-4">
                    <select name="status" class="form-select">
                        @foreach(['pending','processing','completed','cancelled'] as $status)
                            <option value="{{ $status }}"
                                {{ old('status','pending') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TOTALS --}}
                <div class="card border mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Order Totals</h6>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <strong id="order-subtotal">0.00</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Fees</span>
                            <input type="number" step="0.01" name="fees" id="fees"
                                   value="{{ old('fees',0) }}"
                                   class="form-control w-25 text-end">
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <strong class="fs-5 text-primary" id="order-total">0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary">
                        <i class="ti tabler-device-floppy"></i> Create Order
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
let rowIndex = {{ count(old('items', [['quantity'=>1]])) }};

/* ADD ROW */
$('#add-row').on('click', function () {
    let row = `
    <tr>
        <td>
            <select class="form-select product-select">
                <option value="">-- Select Product --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select class="form-select offer-select" name="items[${rowIndex}][seller_offer_id]" disabled required>
                <option value="">-- Select Offer --</option>
            </select>
        </td>
        <td>
            <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty text-center" value="1" min="1">
        </td>
        <td>
            <input type="text" class="form-control subtotal text-end" value="0.00" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                <i class="ti tabler-trash"></i>
            </button>
        </td>
    </tr>`;
    $('#products-table tbody').append(row);
    rowIndex++;
});

/* REMOVE ROW */
$(document).on('click', '.btn-remove', function () {
    $(this).closest('tr').remove();
    calculateTotals();
});

/* LOAD OFFERS */
$(document).on('change', '.product-select', function () {
    let row = $(this).closest('tr');
    let offerSelect = row.find('.offer-select');
    let productId = $(this).val();

    offerSelect.prop('disabled', true).html('<option>Loading...</option>');

    if(productId){
        $.get('/dashboard/products/' + productId + '/offers', function(data){
            let options = '<option value="">-- Select Offer --</option>';
            data.forEach(offer => {
                options += `<option value="${offer.id}" data-price="${offer.retail_price}">
                    ${offer.seller} — $${offer.retail_price}
                </option>`;
            });
            offerSelect.html(options).prop('disabled', false);
        });
    }
});

/* CALCULATE ROW & TOTALS */
$(document).on('change input', '.offer-select, .qty, #fees', function () {
    calculateTotals();
});

function calculateTotals() {
    let subtotal = 0;

    $('#products-table tbody tr').each(function () {
        let row = $(this);
        let price = parseFloat(row.find('.offer-select option:selected').data('price')) || 0;
        let qty = parseInt(row.find('.qty').val()) || 0;
        let line = price * qty;

        row.find('.subtotal').val(line.toFixed(2));
        subtotal += line;
    });

    $('#order-subtotal').text(subtotal.toFixed(2));

    let fees = parseFloat($('#fees').val()) || 0;
    let total = subtotal + fees;

    $('#order-total').text(total.toFixed(2));
}

/* RESTORE OLD OFFERS */
$(document).ready(function () {

    /* Autofill billing from user */
    $('#buyer_id').on('change', function () {
        let option = $(this).find(':selected');
        $('#billing_name').val(option.data('name') || '');
        $('#billing_email').val(option.data('email') || '');
    });

    $('#products-table tbody tr').each(function () {
        let row = $(this);
        let productSelect = row.find('.product-select');
        let offerSelect = row.find('.offer-select');
        let oldOffer = offerSelect.data('old');

        if (productSelect.val()) {
            $.get('/dashboard/products/' + productSelect.val() + '/offers', function (data) {
                let options = '<option value="">-- Select Offer --</option>';
                data.forEach(offer => {
                    options += `<option value="${offer.id}" data-price="${offer.retail_price}"
                        ${offer.id == oldOffer ? 'selected' : ''}>
                        ${offer.seller} — $${offer.retail_price}
                    </option>`;
                });
                offerSelect.html(options).prop('disabled', false);
                calculateTotals();
            });
        }
    });
});
</script>
@endpush
