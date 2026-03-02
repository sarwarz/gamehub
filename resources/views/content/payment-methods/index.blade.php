@extends('layouts.app')
@section('title', 'Payment Methods')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<style>
    .gateway-list .list-group-item {
        border: 1px solid #e9ecef;
        margin-bottom: .5rem;
        border-radius: .5rem !important;
        transition: all .2s;
        cursor: pointer;
    }
    .gateway-list .list-group-item:hover {
        border-color: #7367f0;
        background: #f8f7ff;
    }
    .gateway-list .list-group-item.active {
        background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);
        border-color: #7367f0;
        color: #fff;
    }
    .gateway-list .list-group-item.active .badge {
        background: rgba(255,255,255,.25) !important;
        color: #fff !important;
    }
    .gateway-list .list-group-item.active .gateway-type {
        color: rgba(255,255,255,.7) !important;
    }
    .gateway-icon {
        width: 42px;
        height: 42px;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .gateway-list .list-group-item.active .gateway-icon {
        background: rgba(255,255,255,.2) !important;
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @include('partials.alerts')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-credit-card me-1 text-primary"></i> Payment Methods</h4>
            <p class="text-muted mb-0">Configure payment gateways for your marketplace</p>
        </div>
    </div>

    <div class="row">
        {{-- LEFT SIDEBAR --}}
        <div class="col-lg-4 col-xl-3 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0"><i class="ti tabler-list me-1"></i> Gateways</h6>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush gateway-list">
                        @foreach($methods as $method)
                            @php
                                $icons = [
                                    'paypal'    => ['icon' => 'tabler-brand-paypal',    'bg' => 'bg-label-primary'],
                                    'stripe'    => ['icon' => 'tabler-brand-stripe',    'bg' => 'bg-label-info'],
                                    'cryptomus' => ['icon' => 'tabler-currency-bitcoin','bg' => 'bg-label-warning'],
                                    'tazapay'   => ['icon' => 'tabler-shield-check',   'bg' => 'bg-label-success'],
                                    '1d3'       => ['icon' => 'tabler-world',           'bg' => 'bg-label-dark'],
                                    'cod'       => ['icon' => 'tabler-cash',            'bg' => 'bg-label-secondary'],
                                ];
                                $iconData = $icons[$method->code] ?? ['icon' => 'tabler-credit-card', 'bg' => 'bg-label-secondary'];
                            @endphp
                            <a href="{{ route('payment-methods.edit', $method->code) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3
                               {{ $activeMethod->code === $method->code ? 'active' : '' }}">

                                <div class="gateway-icon {{ $iconData['bg'] }}">
                                    <i class="ti {{ $iconData['icon'] }}"></i>
                                </div>

                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ $method->name }}</span>
                                    <small class="gateway-type text-muted text-capitalize">{{ $method->type }}</small>
                                </div>

                                @if($method->is_enabled)
                                    <span class="badge bg-label-success">On</span>
                                @else
                                    <span class="badge bg-label-secondary">Off</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="col-lg-8 col-xl-9">
            @include('content.payment-methods.form', ['method' => $activeMethod])
        </div>
    </div>

</div>
@endsection

@push('page-js')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
$(function () {
    $('.select2').select2({ dropdownParent: $('body') });
});
</script>
@endpush
