@extends('layouts.app')
@section('title', 'Currencies')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endpush

@section('content')
<div class="app-ecommerce-currencies">

    @include('partials.alerts')

    <div class="card p-2">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Currencies</h5>
            <div>
                <button class="btn btn-warning me-2" id="update-rates" data-url="{{ route('currencies.updateRates') }}">
                    <i class="menu-icon icon-base ti tabler-refresh"></i> Update Rates
                </button>
                <button class="btn btn-danger" id="bulk-delete" data-url="{{ route('currencies.bulk-delete') }}">
                    <i class="menu-icon icon-base ti tabler-trash"></i> Delete Selected
                </button>
                <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCurrencyForm">
                    <i class="menu-icon icon-base ti tabler-plus"></i> Add Currency
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="currencies-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="select-all"></th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Offcanvas Add/Edit -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCurrencyForm">
        <div class="offcanvas-header py-4">
            <h5 class="offcanvas-title" id="form-title">Add Currency</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body border-top">
            <form method="POST" action="{{ route('currencies.store') }}" id="currency-form">
                @csrf
                <input type="hidden" name="currency_id" id="currency_id">

                <div class="mb-3">
                    <label class="form-label">Currency Code</label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="e.g. EUR" required>
                    <small class="text-muted">
                        See common currency codes 
                        <a href="https://gist.github.com/ksafranski/2973986#file-common-currency-json" 
                        target="_blank" rel="noopener noreferrer">
                        here
                        </a>.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Currency Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Euro" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Symbol</label>
                    <input type="text" name="symbol" id="symbol" class="form-control" placeholder="€">
                </div>

                <div class="mb-3">
                    <label class="form-label">Set as Default</label>
                    <select name="is_default" id="is_default" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="is_active" class="form-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
let table = $('#currencies-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route('currencies.index') }}',
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'code' },
        { data: 'name' },
        { data: 'symbol' },
        { data: 'rate' },
        { data: 'default_badge', orderable: false, searchable: false },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
    ]
});

// Reset form when offcanvas opens for Add
$('#offcanvasCurrencyForm').on('show.bs.offcanvas', function (event) {
    let button = $(event.relatedTarget);
    if (button.data('edit')) {
        $('#form-title').text('Edit Currency');
        $('#currency-form').attr('action', button.data('url'));
        $('#currency-form').append('<input type="hidden" name="_method" value="PUT">');

        $('#currency_id').val(button.data('id'));
        $('#code').val(button.data('code'));
        $('#name').val(button.data('name'));
        $('#symbol').val(button.data('symbol'));
        $('#is_default').val(button.data('is_default') ? 1 : 0);
        $('#is_active').val(button.data('is_active') ? 1 : 0);
    } else {
        $('#form-title').text('Add Currency');
        $('#currency-form').attr('action', '{{ route('currencies.store') }}');
        $('#currency-form input[name="_method"]').remove();
        $('#currency-form')[0].reset();
    }
});

$('#update-rates').on('click', function () {
    let url = $(this).data('url');
    let btn = $(this);
    btn.prop('disabled', true).text('Updating...');

    $.post(url, {_token: '{{ csrf_token() }}'}, function (res) {
        if (res.status === 'success') {
            alert(res.message);
            table.ajax.reload();
        } else {
            alert(res.message || 'Error updating rates');
        }
        btn.prop('disabled', false).html('<i class="menu-icon icon-base ti tabler-refresh"></i> Update Rates');
    }).fail(function (xhr) {
        alert('Something went wrong: ' + xhr.responseText);
        btn.prop('disabled', false).html('<i class="menu-icon icon-base ti tabler-refresh"></i> Update Rates');
    });
});

</script>
@endpush
