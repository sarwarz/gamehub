@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-ecommerce.css') }}" />
<style>
.offer-product-preview {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
    background: var(--bs-body-bg);
    transition: all .3s;
}
.offer-product-preview .preview-img {
    width: 100px;
    height: 100px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}
.offer-product-preview .preview-badges .badge {
    font-size: .7rem;
    font-weight: 500;
}
.pricing-tier {
    border: 1px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1.25rem;
    background: var(--bs-body-bg);
    transition: all .25s;
    position: relative;
}
.pricing-tier:hover {
    border-color: rgba(115, 103, 240, .3);
}
.pricing-tier .tier-badge {
    position: absolute;
    top: -10px;
    left: 16px;
    font-size: .7rem;
    padding: 2px 10px;
    border-radius: 20px;
}
.pricing-breakdown {
    background: rgba(115, 103, 240, .04);
    border-radius: 8px;
    padding: .75rem 1rem;
    margin-top: .75rem;
}
.pricing-breakdown .breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: .82rem;
}
.pricing-breakdown .breakdown-item:not(:last-child) {
    border-bottom: 1px dashed var(--bs-border-color);
}
.pricing-breakdown .breakdown-value {
    font-weight: 600;
}
.pricing-breakdown .profit-positive { color: #28c76f; }
.pricing-breakdown .profit-negative { color: #ea5455; }
.keys-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: 12px;
    background: var(--bs-body-bg);
    transition: all .25s;
    position: relative;
}
.keys-zone:focus-within {
    border-color: #7367f0;
    background: rgba(115, 103, 240, .02);
}
.keys-zone textarea {
    border: none;
    background: transparent;
    resize: none;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: .82rem;
    line-height: 1.8;
}
.keys-zone textarea:focus {
    box-shadow: none;
}
.key-counter {
    position: absolute;
    bottom: 10px;
    right: 14px;
    font-size: .72rem;
    color: var(--bs-secondary-color);
    background: var(--bs-body-bg);
    padding: 2px 8px;
    border-radius: 4px;
}
.sale-mode-card {
    cursor: pointer;
    border: 2px solid var(--bs-border-color);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
    transition: all .25s;
}
.sale-mode-card:hover {
    border-color: rgba(115, 103, 240, .3);
}
.sale-mode-card.selected {
    border-color: #7367f0;
    background: rgba(115, 103, 240, .06);
}
.sale-mode-card i {
    font-size: 1.5rem;
    display: block;
    margin-bottom: .35rem;
    color: #7367f0;
}
.sale-mode-card small {
    font-size: .7rem;
    color: var(--bs-secondary-color);
}
.sidebar-card .card-header {
    padding: .875rem 1.25rem;
}
.sidebar-card .card-header h6 {
    font-size: .875rem;
}
.sidebar-card .card-body {
    padding: 1rem 1.25rem;
}
.status-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: all .2s;
    border: 2px solid transparent;
    margin-bottom: 6px;
}
.status-option:hover {
    background: rgba(115, 103, 240, .04);
}
.status-option.selected {
    border-color: #7367f0;
    background: rgba(115, 103, 240, .06);
}
.status-option input[type="radio"] { display: none; }
.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-dot.active { background: #28c76f; }
.status-dot.inactive { background: #a8aaae; }
.status-dot.suspended { background: #ea5455; }
.status-dot.draft { background: #00cfe8; }
</style>
@endpush

@php
    $o = $offer ?? null;
    $isEdit = !is_null($o);
    $currencyService = app(\App\Services\CurrencyService::class);
    $currencyCode = $currencyService->code();
    $currencySymbol = $currencyService->symbol();
@endphp

<div class="row">
    {{-- ===== LEFT COLUMN ===== --}}
    <div class="col-12 col-lg-8">

        {{-- Product & Seller Selection --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <h6 class="mb-0"><i class="ti tabler-package me-2 text-primary"></i>Product & Seller</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="offer-product-select"
                                class="form-select select2 @error('product_id') is-invalid @enderror"
                                data-placeholder="Search for a product..." required>
                            <option value=""></option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ old('product_id', $o->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                {{ $product->title }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Seller <span class="text-danger">*</span></label>
                        <select name="seller_id" id="offer-seller-select"
                                class="form-select select2 @error('seller_id') is-invalid @enderror"
                                data-placeholder="Search for a seller..." required>
                            <option value=""></option>
                            @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}"
                                {{ old('seller_id', $o->seller_id ?? '') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->store_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('seller_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Preview --}}
        <div class="offer-product-preview mb-4 p-3" id="product-preview"
             style="{{ $isEdit ? '' : 'display:none' }}">
            <div class="d-flex align-items-center gap-3">
                <img id="preview-cover" class="preview-img"
                     src="{{ $isEdit && $o->product?->image ? asset($o->product->image) : asset('assets/img/default-product.png') }}"
                     alt="Product">
                <div class="flex-grow-1">
                    <h6 id="preview-title" class="mb-2">{{ $isEdit ? $o->product?->title : '' }}</h6>
                    <div class="d-flex flex-wrap gap-1 preview-badges">
                        <span class="badge bg-label-primary" id="preview-type">{{ $isEdit ? ($o->product?->types->pluck('name')->join(', ') ?: '-') : '' }}</span>
                        <span class="badge bg-label-info" id="preview-platform">{{ $isEdit ? ($o->product?->platforms->pluck('name')->join(', ') ?: '-') : '' }}</span>
                        <span class="badge bg-label-warning" id="preview-region">{{ $isEdit ? ($o->product?->regions->pluck('name')->join(', ') ?: '-') : '' }}</span>
                        <span class="badge bg-label-success" id="preview-language">{{ $isEdit ? ($o->product?->languages->pluck('name')->join(', ') ?: '-') : '' }}</span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Commission Rate: <strong id="preview-commission" class="text-primary">{{ $isEdit ? (optional($o->product?->types->first())->commission ?? 0) : 0 }}%</strong></small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Retail Pricing --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-receipt me-2 text-primary"></i>Retail Pricing</h6>
                    <span class="badge bg-label-primary">{{ $currencyCode }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="pricing-tier">
                    <span class="tier-badge badge bg-primary">Single Key</span>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="retail_price" id="retail_price"
                                       class="form-control pricing-input @error('retail_price') is-invalid @enderror"
                                       value="{{ old('retail_price', $o->retail_price ?? '0.00') }}" min="0">
                            </div>
                            @error('retail_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Acquisition Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="retail_acquisition_cost" id="retail_acquisition_cost"
                                       class="form-control pricing-input"
                                       value="{{ old('retail_acquisition_cost', $o->retail_acquisition_cost ?? '0.00') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profit</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-trending-up ti-xs"></i></span>
                                <input type="text" id="retail_profit" class="form-control fw-semibold" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="pricing-breakdown" id="retail-breakdown">
                        <div class="breakdown-item">
                            <span>Commission (<span class="commission-rate-display">0</span>%)</span>
                            <span class="breakdown-value" id="retail_commission">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Seller Share</span>
                            <span class="breakdown-value text-success" id="retail_share">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Net Profit</span>
                            <span class="breakdown-value" id="retail_net">{{ $currencySymbol }}0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wholesale Pricing --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-packages me-2 text-primary"></i>Wholesale Pricing</h6>
                    <span class="badge bg-label-primary">{{ $currencyCode }}</span>
                </div>
            </div>
            <div class="card-body">
                {{-- Tier 1: 10-99 Keys --}}
                <div class="pricing-tier mb-4">
                    <span class="tier-badge badge bg-info">10 &ndash; 99 Keys</span>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Selling Price</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="wholesale_10_99_price" id="wholesale_10_99_price"
                                       class="form-control pricing-input"
                                       value="{{ old('wholesale_10_99_price', $o->wholesale_10_99_price ?? '0.00') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Acquisition Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="wholesale_10_99_acquisition_cost" id="wholesale_10_99_acquisition_cost"
                                       class="form-control pricing-input"
                                       value="{{ old('wholesale_10_99_acquisition_cost', $o->wholesale_10_99_acquisition_cost ?? '0.00') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profit</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-trending-up ti-xs"></i></span>
                                <input type="text" id="wholesale_10_99_profit" class="form-control fw-semibold" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="pricing-breakdown" id="ws1-breakdown">
                        <div class="breakdown-item">
                            <span>Commission (<span class="commission-rate-display">0</span>%)</span>
                            <span class="breakdown-value" id="wholesale_10_99_commission">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Seller Share</span>
                            <span class="breakdown-value text-success" id="wholesale_10_99_share">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Net Profit</span>
                            <span class="breakdown-value" id="wholesale_10_99_net">{{ $currencySymbol }}0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Tier 2: 100+ Keys --}}
                <div class="pricing-tier">
                    <span class="tier-badge badge bg-warning">100+ Keys</span>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label">Selling Price</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="wholesale_100_plus_price" id="wholesale_100_price"
                                       class="form-control pricing-input"
                                       value="{{ old('wholesale_100_plus_price', $o->wholesale_100_plus_price ?? '0.00') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Acquisition Cost</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input type="number" step="0.01" name="wholesale_100_acquisition_cost" id="wholesale_100_acquisition_cost"
                                       class="form-control pricing-input"
                                       value="{{ old('wholesale_100_acquisition_cost', $o->wholesale_100_acquisition_cost ?? '0.00') }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Profit</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti tabler-trending-up ti-xs"></i></span>
                                <input type="text" id="wholesale_100_profit" class="form-control fw-semibold" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="pricing-breakdown" id="ws2-breakdown">
                        <div class="breakdown-item">
                            <span>Commission (<span class="commission-rate-display">0</span>%)</span>
                            <span class="breakdown-value" id="wholesale_100_commission">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Seller Share</span>
                            <span class="breakdown-value text-success" id="wholesale_100_share">{{ $currencySymbol }}0.00</span>
                        </div>
                        <div class="breakdown-item">
                            <span>Net Profit</span>
                            <span class="breakdown-value" id="wholesale_100_net">{{ $currencySymbol }}0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Keys --}}
        <div class="card mb-4">
            <div class="card-header pb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ti tabler-key me-2 text-primary"></i>Product Keys</h6>
                    @if($isEdit)
                    <span class="badge bg-label-secondary">
                        <i class="ti tabler-database ti-xs me-1"></i>
                        <span id="total-keys-count">{{ $o->keys->count() }}</span> keys stored
                    </span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="keys-zone p-3">
                    <textarea name="keys_text" id="keys-textarea" rows="8"
                              class="form-control @error('keys_text') is-invalid @enderror"
                              placeholder="XXXXX-XXXXX-XXXXX&#10;YYYYY-YYYYY-YYYYY&#10;ZZZZZ-ZZZZZ-ZZZZZ&#10;&#10;Enter one key per line...">{{ old('keys_text', $isEdit ? $o->keys->pluck('value')->join("\n") : '') }}</textarea>
                    <span class="key-counter"><i class="ti tabler-key ti-xs me-1"></i><span id="key-line-count">0</span> keys</span>
                    @error('keys_text') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="d-flex align-items-center gap-3 mt-3">
                    <small class="text-muted"><i class="ti tabler-info-circle ti-xs me-1"></i>One key per line. Duplicates and empty lines are automatically removed.</small>
                </div>
                @if($isEdit)
                <div class="alert alert-warning mt-3 mb-0 py-2">
                    <i class="ti tabler-alert-triangle me-1"></i>
                    <small>Saving will replace <strong>all existing available keys</strong> with the ones entered above.</small>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== RIGHT COLUMN ===== --}}
    <div class="col-12 col-lg-4">

        {{-- Sale Mode --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-shopping-cart me-2 text-primary"></i>Sale Mode</h6>
            </div>
            <div class="card-body">
                @php $saleMode = old('sale_mode', $o->sale_mode ?? 'both'); @endphp
                <div class="d-flex gap-2">
                    <label class="sale-mode-card flex-fill {{ $saleMode === 'retail' ? 'selected' : '' }}">
                        <input type="radio" name="sale_mode" value="retail" class="sale-mode-radio"
                               {{ $saleMode === 'retail' ? 'checked' : '' }}>
                        <i class="ti tabler-user"></i>
                        <strong class="d-block">Retail</strong>
                        <small>Single keys</small>
                    </label>
                    <label class="sale-mode-card flex-fill {{ $saleMode === 'wholesale' ? 'selected' : '' }}">
                        <input type="radio" name="sale_mode" value="wholesale" class="sale-mode-radio"
                               {{ $saleMode === 'wholesale' ? 'checked' : '' }}>
                        <i class="ti tabler-users"></i>
                        <strong class="d-block">Wholesale</strong>
                        <small>Bulk orders</small>
                    </label>
                    <label class="sale-mode-card flex-fill {{ $saleMode === 'both' ? 'selected' : '' }}">
                        <input type="radio" name="sale_mode" value="both" class="sale-mode-radio"
                               {{ $saleMode === 'both' ? 'checked' : '' }}>
                        <i class="ti tabler-arrows-exchange"></i>
                        <strong class="d-block">Both</strong>
                        <small>All channels</small>
                    </label>
                </div>
                @error('sale_mode') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Status --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-toggle-left me-2 text-primary"></i>Status</h6>
            </div>
            <div class="card-body p-3">
                @php $status = old('status', $o->status ?? 'active'); @endphp
                @foreach(['active' => 'Ready to sell', 'inactive' => 'Hidden from listings', 'draft' => 'Work in progress', 'suspended' => 'Temporarily blocked'] as $val => $desc)
                <label class="status-option {{ $status === $val ? 'selected' : '' }}">
                    <input type="radio" name="status" value="{{ $val }}" {{ $status === $val ? 'checked' : '' }}>
                    <span class="status-dot {{ $val }}"></span>
                    <div>
                        <strong class="d-block" style="font-size:.85rem">{{ ucfirst($val) }}</strong>
                        <small class="text-muted">{{ $desc }}</small>
                    </div>
                </label>
                @endforeach
                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Flags --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-flag me-2 text-primary"></i>Offer Flags</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3 p-2 rounded" style="background: rgba(40, 199, 111, .06)">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti tabler-rosette-discount-check text-success" style="font-size: 1.2rem"></i>
                        <div>
                            <strong class="d-block" style="font-size:.85rem">Verified</strong>
                            <small class="text-muted">Trusted seller badge</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" name="is_verified" value="1" id="is-verified"
                               {{ old('is_verified', $o->is_verified ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(255, 159, 67, .06)">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ti tabler-speakerphone text-warning" style="font-size: 1.2rem"></i>
                        <div>
                            <strong class="d-block" style="font-size:.85rem">Promoted</strong>
                            <small class="text-muted">Boosted in listings</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input" name="is_promoted" value="1" id="is-promoted"
                               {{ old('is_promoted', $o->is_promoted ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Summary --}}
        <div class="card sidebar-card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti tabler-chart-bar me-2 text-primary"></i>Quick Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Retail Price</span>
                    <strong class="small" id="summary-retail">{{ $currencySymbol }}{{ number_format($o->retail_price ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Wholesale (10-99)</span>
                    <strong class="small" id="summary-ws1">{{ $currencySymbol }}{{ number_format($o->wholesale_10_99_price ?? 0, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Wholesale (100+)</span>
                    <strong class="small" id="summary-ws2">{{ $currencySymbol }}{{ number_format($o->wholesale_100_plus_price ?? 0, 2) }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Keys</span>
                    <strong class="small" id="summary-keys">0</strong>
                </div>
            </div>
        </div>

    </div>
</div>

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const SYMBOL = '{{ $currencySymbol }}';
    let COMMISSION_RATE = {{ $isEdit ? (optional($o->product?->types->first())->commission ?? 0) : 0 }};

    // ============================
    // Product Preview & Commission
    // ============================
    const productSelect = document.getElementById('offer-product-select');
    const previewBox = document.getElementById('product-preview');

    function fetchProductPreview(productId) {
        if (!productId) {
            previewBox.style.display = 'none';
            COMMISSION_RATE = 0;
            updateAllBreakdowns();
            return;
        }

        fetch("{{ url('dashboard/products') }}/" + productId + "/preview")
            .then(r => r.json())
            .then(data => {
                document.getElementById('preview-cover').src = data.image || '{{ asset("assets/img/default-product.png") }}';
                document.getElementById('preview-title').textContent = data.title;
                document.getElementById('preview-type').textContent = data.types?.join(', ') || '-';
                document.getElementById('preview-platform').textContent = data.platforms?.join(', ') || '-';
                document.getElementById('preview-region').textContent = data.regions?.join(', ') || '-';
                document.getElementById('preview-language').textContent = data.languages?.join(', ') || '-';
                document.getElementById('preview-commission').textContent = (data.commission || 0) + '%';

                COMMISSION_RATE = data.commission || 0;
                document.querySelectorAll('.commission-rate-display').forEach(el => el.textContent = COMMISSION_RATE);

                previewBox.style.display = '';
                updateAllBreakdowns();
            })
            .catch(() => {
                previewBox.style.display = 'none';
            });
    }

    $(productSelect).on('change', function() {
        fetchProductPreview(this.value);
    });

    // ============================
    // Pricing Calculator
    // ============================
    function calcTier(priceId, costId, commId, shareId, netId, profitId) {
        const price = parseFloat(document.getElementById(priceId).value) || 0;
        const cost = parseFloat(document.getElementById(costId).value) || 0;
        const commission = price * COMMISSION_RATE / 100;
        const share = price - commission;
        const profit = share - cost;

        document.getElementById(commId).textContent = SYMBOL + commission.toFixed(2);
        document.getElementById(shareId).textContent = SYMBOL + share.toFixed(2);
        document.getElementById(netId).textContent = SYMBOL + profit.toFixed(2);
        document.getElementById(netId).className = 'breakdown-value ' + (profit >= 0 ? 'profit-positive' : 'profit-negative');
        document.getElementById(profitId).value = SYMBOL + profit.toFixed(2);
        document.getElementById(profitId).style.color = profit >= 0 ? '#28c76f' : '#ea5455';

        return price;
    }

    function updateAllBreakdowns() {
        document.querySelectorAll('.commission-rate-display').forEach(el => el.textContent = COMMISSION_RATE);

        const rp = calcTier('retail_price', 'retail_acquisition_cost', 'retail_commission', 'retail_share', 'retail_net', 'retail_profit');
        const wp1 = calcTier('wholesale_10_99_price', 'wholesale_10_99_acquisition_cost', 'wholesale_10_99_commission', 'wholesale_10_99_share', 'wholesale_10_99_net', 'wholesale_10_99_profit');
        const wp2 = calcTier('wholesale_100_price', 'wholesale_100_acquisition_cost', 'wholesale_100_commission', 'wholesale_100_share', 'wholesale_100_net', 'wholesale_100_profit');

        document.getElementById('summary-retail').textContent = SYMBOL + rp.toFixed(2);
        document.getElementById('summary-ws1').textContent = SYMBOL + wp1.toFixed(2);
        document.getElementById('summary-ws2').textContent = SYMBOL + wp2.toFixed(2);
    }

    document.querySelectorAll('.pricing-input').forEach(inp => {
        inp.addEventListener('input', updateAllBreakdowns);
    });

    // ============================
    // Key Counter
    // ============================
    const keysTextarea = document.getElementById('keys-textarea');
    const keyCountDisplay = document.getElementById('key-line-count');
    const summaryKeys = document.getElementById('summary-keys');

    function countKeys() {
        const lines = keysTextarea.value.split('\n').filter(l => l.trim() !== '');
        keyCountDisplay.textContent = lines.length;
        summaryKeys.textContent = lines.length;
    }
    keysTextarea.addEventListener('input', countKeys);

    // ============================
    // Sale Mode Cards
    // ============================
    document.querySelectorAll('.sale-mode-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.sale-mode-card').forEach(c => c.classList.remove('selected'));
            this.closest('.sale-mode-card').classList.add('selected');
        });
    });

    // ============================
    // Status Options
    // ============================
    document.querySelectorAll('.status-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.status-option').forEach(o => o.classList.remove('selected'));
            this.closest('.status-option').classList.add('selected');
        });
    });

    // ============================
    // Initialize
    // ============================
    countKeys();
    updateAllBreakdowns();

    @if($isEdit)
    // Load commission from existing product on edit
    fetchProductPreview(productSelect.value);
    @endif
});
</script>
@endpush
