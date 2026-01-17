<div class="tab-content p-0">
    <div class="tab-pane fade show active" id="payments" role="tabpanel">

        <div class="card">
            <div class="card-body">

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('payment-methods.update', $method->code) }}">
                    @csrf

                    {{-- ================= STATUS ================= --}}
                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Status</label>

                        <label class="switch switch-success">
                            <input type="checkbox"
                                   name="is_enabled"
                                   value="1"
                                   class="switch-input"
                                   {{ $method->is_enabled ? 'checked' : '' }}>
                            <span class="switch-toggle-slider">
                                <span class="switch-on">
                                    <i class="icon-base ti tabler-check"></i>
                                </span>
                                <span class="switch-off">
                                    <i class="icon-base ti tabler-x"></i>
                                </span>
                            </span>
                            <span class="switch-label">
                                {{ $method->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </label>
                    </div>

                    {{-- ================= MODE ================= --}}
                    <div class="mb-3">
                        <label class="form-label">Account Mode</label>
                        <select name="mode" class="form-select">
                            <option value="sandbox" {{ $method->mode === 'sandbox' ? 'selected' : '' }}>
                                Sandbox
                            </option>
                            <option value="live" {{ $method->mode === 'live' ? 'selected' : '' }}>
                                Live
                            </option>
                        </select>
                    </div>

                    {{-- ================= COUNTRY ================= --}}
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input type="text"
                               name="country"
                               class="form-control"
                               value="{{ $method->country }}">
                    </div>

                    {{-- ================= CURRENCY ================= --}}
                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select select2">
                            <option value="">Select currency</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->code }}"
                                    {{ $method->currency === $currency->code ? 'selected' : '' }}>
                                    {{ $currency->name }} ({{ $currency->code }}) {{ $currency->symbol }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ================= RATE ================= --}}
                    <div class="mb-4">
                        <label class="form-label">Currency Rate (Per USD)</label>
                        <input type="number"
                               step="0.000001"
                               name="rate"
                               class="form-control"
                               value="{{ $method->rate }}">
                    </div>

                    {{-- ================= CONFIG ================= --}}
                    @php($config = $method->config ?? [])

                    {{-- ===== PayPal ===== --}}
                    @if($method->code === 'paypal')
                        <h6 class="mb-3">PayPal Settings</h6>

                        <div class="mb-3">
                            <label>Client ID</label>
                            <input type="text" class="form-control"
                                   name="config[client_id]"
                                   value="{{ $config['client_id'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Secret Key</label>
                            <input type="password" class="form-control"
                                   name="config[secret_key]"
                                   value="{{ $config['secret_key'] ?? '' }}">
                        </div>
                    @endif

                    {{-- ===== Stripe ===== --}}
                    @if($method->code === 'stripe')
                        <h6 class="mb-3">Stripe Settings</h6>

                        <div class="mb-3">
                            <label>Publishable Key</label>
                            <input type="text" class="form-control"
                                   name="config[publishable_key]"
                                   value="{{ $config['publishable_key'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Secret Key</label>
                            <input type="password" class="form-control"
                                   name="config[secret_key]"
                                   value="{{ $config['secret_key'] ?? '' }}">
                        </div>
                    @endif

                    {{-- ===== Cryptomus ===== --}}
                    @if($method->code === 'cryptomus')
                        <h6 class="mb-3">Cryptomus Settings</h6>

                        <div class="mb-3">
                            <label>Merchant ID</label>
                            <input type="text" class="form-control"
                                   name="config[merchant_id]"
                                   value="{{ $config['merchant_id'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>API Key</label>
                            <input type="password" class="form-control"
                                   name="config[api_key]"
                                   value="{{ $config['api_key'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Webhook Secret</label>
                            <input type="password" class="form-control"
                                   name="config[webhook_secret]"
                                   value="{{ $config['webhook_secret'] ?? '' }}">
                        </div>
                    @endif

                    {{-- ===== Tazapay ===== --}}
                    @if($method->code === 'tazapay')
                        <h6 class="mb-3">Tazapay Settings</h6>

                        <div class="mb-3">
                            <label>Merchant ID</label>
                            <input type="text" class="form-control"
                                   name="config[merchant_id]"
                                   value="{{ $config['merchant_id'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>API Key</label>
                            <input type="password" class="form-control"
                                   name="config[api_key]"
                                   value="{{ $config['api_key'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>API Secret</label>
                            <input type="password" class="form-control"
                                   name="config[api_secret]"
                                   value="{{ $config['api_secret'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Environment</label>
                            <select name="config[environment]" class="form-select">
                                <option value="sandbox"
                                    {{ ($config['environment'] ?? '') === 'sandbox' ? 'selected' : '' }}>
                                    Sandbox
                                </option>
                                <option value="live"
                                    {{ ($config['environment'] ?? '') === 'live' ? 'selected' : '' }}>
                                    Live
                                </option>
                            </select>
                        </div>
                    @endif

                    {{-- ===== 1D3 ===== --}}
                    @if($method->code === '1d3')
                        <h6 class="mb-3">1D3 Settings</h6>

                        <div class="mb-3">
                            <label>Merchant Number</label>
                            <input type="text" class="form-control"
                                   name="config[merchant_no]"
                                   value="{{ $config['merchant_no'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Terminal ID</label>
                            <input type="text" class="form-control"
                                   name="config[terminal_id]"
                                   value="{{ $config['terminal_id'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Secret Key</label>
                            <input type="password" class="form-control"
                                   name="config[secret_key]"
                                   value="{{ $config['secret_key'] ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label>Callback URL</label>
                            <input type="url" class="form-control"
                                   name="config[callback_url]"
                                   value="{{ $config['callback_url'] ?? '' }}">
                        </div>
                    @endif

                    {{-- ================= SAVE ================= --}}
                    <div class="mt-4">
                        <button class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>
