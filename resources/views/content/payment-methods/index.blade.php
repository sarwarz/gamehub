@extends('layouts.app')
@section('title', 'Payment Methods')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Payment Methods</h4>

    <div class="card"> 
        <div class="card-body">
            <div class="row">
                {{-- LEFT SIDEBAR --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-bodys">
                            <div class="list-group">
                                @foreach($methods as $method)
                                    <a href="{{ route('payment-methods.edit', $method->code) }}"
                                    class="list-group-item list-group-item-action py-3 d-flex justify-content-between align-items-center
                                    {{ $activeMethod->code === $method->code ? 'active' : '' }}">

                                        <span>{{ $method->name }}</span>

                                        @if($method->is_enabled)
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </a>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT PANEL --}}

                <div class="col-md-8">
                    @include('content.payment-methods.form', ['method' => $activeMethod])
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
