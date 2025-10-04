@extends('layouts.app')
@section('title', 'Add Order')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
@endpush

@section('content')
<div class="app-ecommerce-order-create">

    @include('partials.alerts')

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

                <!-- Customer -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Customer</label>
                    <select name="buyer_id" class="form-select select2" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product + Offers -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Products & Offers</label>
                    <div class="table-responsive">
                        <table class="table align-middle table-bordered" id="products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Offer</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Product -->
                                    <td style="min-width: 220px">
                                        <select class="form-select product-select" name="items[0][product_id]" required>
                                            <option value="">-- Select Product --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <!-- Offers -->
                                    <td style="min-width: 220px">
                                        <select class="form-select offer-select" name="items[0][offer_id]" required disabled>
                                            <option value="">-- Select Offer --</option>
                                        </select>
                                    </td>

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" name="items[0][unit_price]" class="form-control price text-end" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control qty text-center" value="1" min="1">
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="text" name="items[0][commission]" class="form-control commission text-end" readonly>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control subtotal text-end" readonly>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                                            <i class="ti tabler-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ti tabler-plus"></i> Add Product
                    </button>
                </div>

                <!-- Address -->
                <div class="mb-4">
                    <h6 class="fw-bold">Shipping Address</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="address[full_name]" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="address[email]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="address[phone]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" name="address[country]" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address[address_line1]" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address[address_line2]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="address[city]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="address[state]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="address[postal_code]" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Method</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="e.g. Stripe, PayPal" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Reference</label>
                        <input type="text" name="payment_ref" class="form-control" placeholder="Transaction ID / Ref">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Order Status</label>
                    <select name="status" class="form-select">
                        @foreach(['pending','processing','delivered','completed','refunded','cancelled'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Totals -->
                <div class="card border mt-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Order Totals</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong id="order-subtotal">0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Fees:</span>
                            <input type="number" step="0.01" name="fees" value="0.00" class="form-control w-25 text-end">
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Commission:</span>
                            <input type="number" step="0.01" name="commission" value="0.00" class="form-control w-25 text-end" readonly>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <strong id="order-total" class="text-primary fs-5">0.00</strong>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy"></i> Create Order
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-label-danger">
                        <i class="ti tabler-x"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>


let rowIndex = 1;

// Add new row
$('#add-row').on('click', function () {
    let row = `
        <tr>
            <td>
                <select class="form-select product-select" name="items[${rowIndex}][product_id]" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->title }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select offer-select" name="items[${rowIndex}][offer_id]" required disabled>
                    <option value="">-- Select Offer --</option>
                </select>
            </td>
            <td><div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control price text-end" readonly></div></td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control qty text-center" value="1" min="1"></td>
            <td><div class="input-group"><span class="input-group-text">$</span><input type="text" name="items[${rowIndex}][commission]" class="form-control commission text-end" readonly></div></td>
            <td><div class="input-group"><span class="input-group-text">$</span><input type="text" class="form-control subtotal text-end" readonly></div></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove"><i class="ti tabler-trash"></i></button></td>
        </tr>`;
    $('#products-table tbody').append(row);
    rowIndex++;
});

// Remove row
$(document).on('click', '.btn-remove', function () {
    $(this).closest('tr').remove();
    calculateTotals();
});

// Load offers when product is selected
$(document).on('change', '.product-select', function () {
    let productId = $(this).val();
    let row = $(this).closest('tr');
    let offerSelect = row.find('.offer-select');

    offerSelect.html('<option>Loading...</option>').prop('disabled', true);

    if(productId){
        $.get('/dashboard/products/' + productId + '/offers', function(data){
            let options = '<option value="">-- Select Offer --</option>';
            data.forEach(offer => {
                options += `<option value="${offer.id}" data-price="${offer.retail_price}" data-commission="${offer.commission}">
                                ${offer.seller} — $${offer.retail_price}
                            </option>`;
            });
            offerSelect.html(options).prop('disabled', false);
        });
    }
});

// When offer selected → auto-fill price + commission
$(document).on('change', '.offer-select', function () {
    let price = $(this).find(':selected').data('price');
    let commissionRate = $(this).find(':selected').data('commission');
    let row = $(this).closest('tr');

    row.find('.price').val(price).trigger('input');
    row.find('.commission').val(commissionRate + '%');
});

// Calculate totals
$(document).on('input', '.price, .qty', function () {
    let row = $(this).closest('tr');
    let price = parseFloat(row.find('.price').val()) || 0;
    let qty = parseInt(row.find('.qty').val()) || 0;
    let commRate = parseFloat(row.find('.offer-select option:selected').data('commission')) || 0;

    let subtotal = price * qty;
    let commissionAmount = subtotal * (commRate / 100);

    row.find('.subtotal').val(subtotal.toFixed(2));
    row.find('.commission').val(commissionAmount.toFixed(2));

    calculateTotals();
});

function calculateTotals() {
    let subtotal = 0;
    let commissionTotal = 0;

    $('#products-table tbody tr').each(function () {
        subtotal += parseFloat($(this).find('.subtotal').val()) || 0;
        commissionTotal += parseFloat($(this).find('.commission').val()) || 0;
    });

    $('#order-subtotal').text(subtotal.toFixed(2));
    $('input[name="commission"]').val(commissionTotal.toFixed(2));

    let fees = parseFloat($('input[name="fees"]').val()) || 0;
    let total = subtotal + fees + commissionTotal;
    $('#order-total').text(total.toFixed(2));
}
</script>
@endpush
