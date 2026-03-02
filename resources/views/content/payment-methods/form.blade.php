@php
    $icons = [
        'paypal'    => ['icon' => 'tabler-brand-paypal',    'bg' => 'bg-label-primary',  'color' => '#003087'],
        'stripe'    => ['icon' => 'tabler-brand-stripe',    'bg' => 'bg-label-info',     'color' => '#635bff'],
        'cryptomus' => ['icon' => 'tabler-currency-bitcoin','bg' => 'bg-label-warning',  'color' => '#f7931a'],
        'tazapay'   => ['icon' => 'tabler-shield-check',   'bg' => 'bg-label-success',  'color' => '#28a745'],
        '1d3'       => ['icon' => 'tabler-world',           'bg' => 'bg-label-dark',     'color' => '#333'],
        'cod'       => ['icon' => 'tabler-cash',            'bg' => 'bg-label-secondary','color' => '#6c757d'],
    ];
    $iconData = $icons[$method->code] ?? ['icon' => 'tabler-credit-card', 'bg' => 'bg-label-secondary', 'color' => '#6c757d'];
    $config = $method->config ?? [];
@endphp

<div class="card">
    {{-- Gateway Header --}}
    <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-md {{ $iconData['bg'] }}" style="display:flex;align-items:center;justify-content:center">
                <i class="ti {{ $iconData['icon'] }} ti-lg"></i>
            </div>
            <div>
                <h5 class="mb-0">{{ $method->name }}</h5>
                <small class="text-muted text-capitalize">{{ $method->type }} payment gateway</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($method->mode === 'sandbox')
                <span class="badge bg-label-warning"><i class="ti tabler-test-pipe me-1" style="font-size:.7rem"></i>Sandbox</span>
            @else
                <span class="badge bg-label-success"><i class="ti tabler-circle-check me-1" style="font-size:.7rem"></i>Live</span>
            @endif
            @if($method->is_enabled)
                <span class="badge bg-label-success"><i class="ti tabler-power me-1" style="font-size:.7rem"></i>Enabled</span>
            @else
                <span class="badge bg-label-danger"><i class="ti tabler-power me-1" style="font-size:.7rem"></i>Disabled</span>
            @endif
        </div>
    </div>

    <div class="card-body pt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="ti tabler-circle-check me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('payment-methods.update', $method->code) }}">
            @csrf

            {{-- General Settings --}}
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted fw-semibold small mb-0">
                        <i class="ti tabler-settings me-1"></i> General Settings
                    </h6>
                    <hr class="mt-2 mb-0">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold d-block mb-2">Status</label>
                    <label class="switch switch-success">
                        <input type="checkbox" name="is_enabled" value="1" class="switch-input"
                               {{ $method->is_enabled ? 'checked' : '' }}>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="icon-base ti tabler-check"></i></span>
                            <span class="switch-off"><i class="icon-base ti tabler-x"></i></span>
                        </span>
                        <span class="switch-label">{{ $method->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                    </label>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Account Mode</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-toggle-left"></i></span>
                        <select name="mode" class="form-select">
                            <option value="sandbox" {{ $method->mode === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                            <option value="live" {{ $method->mode === 'live' ? 'selected' : '' }}>Live (Production)</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Currency</label>
                    <select name="currency" class="form-select select2">
                        <option value="">Auto (Default)</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->code }}"
                                {{ $method->currency === $currency->code ? 'selected' : '' }}>
                                {{ $currency->code }} — {{ $currency->name }} ({{ $currency->symbol }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Gateway Credentials --}}
            @if($method->code !== 'cod')
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted fw-semibold small mb-0">
                        <i class="ti tabler-key me-1"></i> API Credentials
                    </h6>
                    <hr class="mt-2 mb-0">
                </div>

                {{-- PayPal --}}
                @if($method->code === 'paypal')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Client ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-id"></i></span>
                        <input type="text" class="form-control" name="config[client_id]"
                               value="{{ $config['client_id'] ?? '' }}" placeholder="PayPal Client ID">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secret Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                        <input type="password" class="form-control" name="config[secret_key]"
                               value="{{ $config['secret_key'] ?? '' }}" placeholder="PayPal Secret Key">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Webhook ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-webhook"></i></span>
                        <input type="text" class="form-control" name="config[webhook_id]"
                               value="{{ $config['webhook_id'] ?? '' }}" placeholder="PayPal Webhook ID (from Developer Dashboard)">
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Find this in PayPal Developer Dashboard &rarr; Webhooks &rarr; Webhook ID. Required for signature verification in live mode.
                    </small>
                </div>
                @endif

                {{-- Stripe --}}
                @if($method->code === 'stripe')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Publishable Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-key"></i></span>
                        <input type="text" class="form-control" name="config[publishable_key]"
                               value="{{ $config['publishable_key'] ?? '' }}" placeholder="pk_live_... or pk_test_...">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secret Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                        <input type="password" class="form-control" name="config[secret_key]"
                               value="{{ $config['secret_key'] ?? '' }}" placeholder="sk_live_... or sk_test_...">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Webhook Secret <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-webhook"></i></span>
                        <input type="password" class="form-control" name="config[webhook_secret]"
                               value="{{ $config['webhook_secret'] ?? '' }}" placeholder="whsec_...">
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Find this in Stripe Dashboard &rarr; Developers &rarr; Webhooks &rarr; Signing secret. Required for live mode.
                    </small>
                </div>
                @endif

                {{-- Cryptomus --}}
                @if($method->code === 'cryptomus')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Merchant ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-id"></i></span>
                        <input type="text" class="form-control" name="config[merchant_id]"
                               value="{{ $config['merchant_id'] ?? '' }}" placeholder="Cryptomus Merchant ID">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">API Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-key"></i></span>
                        <input type="password" class="form-control" name="config[api_key]"
                               value="{{ $config['api_key'] ?? '' }}" placeholder="Cryptomus API Key">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Webhook Secret</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-webhook"></i></span>
                        <input type="password" class="form-control" name="config[webhook_secret]"
                               value="{{ $config['webhook_secret'] ?? '' }}" placeholder="Webhook verification secret">
                    </div>
                </div>
                @endif

                {{-- Tazapay --}}
                @if($method->code === 'tazapay')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Merchant ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-id"></i></span>
                        <input type="text" class="form-control" name="config[merchant_id]"
                               value="{{ $config['merchant_id'] ?? '' }}" placeholder="Tazapay Merchant ID">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">API Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-key"></i></span>
                        <input type="password" class="form-control" name="config[api_key]"
                               value="{{ $config['api_key'] ?? '' }}" placeholder="Tazapay API Key">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">API Secret <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                        <input type="password" class="form-control" name="config[api_secret]"
                               value="{{ $config['api_secret'] ?? '' }}" placeholder="Tazapay API Secret">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Environment</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-toggle-left"></i></span>
                        <select name="config[environment]" class="form-select">
                            <option value="sandbox" {{ ($config['environment'] ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live" {{ ($config['environment'] ?? '') === 'live' ? 'selected' : '' }}>Live</option>
                        </select>
                    </div>
                </div>
                @endif

                {{-- 1D3 --}}
                @if($method->code === '1d3')
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Merchant Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-id"></i></span>
                        <input type="text" class="form-control" name="config[merchant_no]"
                               value="{{ $config['merchant_no'] ?? '' }}" placeholder="1D3 Merchant Number">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Terminal ID <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-device-desktop"></i></span>
                        <input type="text" class="form-control" name="config[terminal_id]"
                               value="{{ $config['terminal_id'] ?? '' }}" placeholder="Terminal ID">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secret Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                        <input type="password" class="form-control" name="config[secret_key]"
                               value="{{ $config['secret_key'] ?? '' }}" placeholder="1D3 Secret Key">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Callback URL</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti tabler-link"></i></span>
                        <input type="url" class="form-control" name="config[callback_url]"
                               value="{{ $config['callback_url'] ?? '' }}" placeholder="https://yourdomain.com/callback">
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- COD Note --}}
            @if($method->code === 'cod')
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="ti tabler-info-circle me-2 ti-md"></i>
                <div>
                    <strong>Cash On Delivery</strong> — No API credentials required. Customers pay upon delivery. Enable/disable and set the currency above.
                </div>
            </div>
            @endif

            {{-- Save --}}
            <div class="d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('payment-methods.index') }}" class="btn btn-label-secondary">
                    <i class="ti tabler-x me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Save Changes
                </button>
            </div>

        </form>
    </div>
</div>
