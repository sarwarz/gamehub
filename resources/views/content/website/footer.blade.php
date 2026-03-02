@extends('layouts.app')

@section('title', 'Footer Settings')

@push('page-css')
<style>
.cms-nav { position: sticky; top: 80px; }
.cms-nav .nav-link {
    display: flex; align-items: center; gap: 10px; padding: 10px 16px;
    border-radius: 8px; color: #566a7f; font-weight: 500; font-size: .9rem; transition: background .2s;
}
.cms-nav .nav-link:hover { background: rgba(115,103,240,.08); color: #7367f0; }
.cms-nav .nav-link.active { background: linear-gradient(135deg,#7367f0,#9e95f5); color: #fff !important; box-shadow: 0 2px 8px rgba(115,103,240,.4); }
.cms-nav .nav-link.active i { color: #fff !important; }
.cms-nav .nav-link i { font-size: 1.2rem; width: 24px; text-align: center; }
.cms-section { display: none; }
.cms-section.active { display: block; }
.section-card { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 1.5rem; }
.section-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem; }
.section-card .card-header h6 { margin: 0; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.section-card .card-body { padding: 1.25rem 1.5rem; }
.form-hint { font-size: .8rem; color: #a1acb8; margin-top: 4px; }
.column-card {
    border: 1px solid #e7e7e8; border-radius: 10px; padding: 16px; margin-bottom: 16px;
    background: #fafafa;
}
.column-card .column-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;
}
.link-row {
    display: flex; gap: 8px; align-items: center; margin-bottom: 8px;
    padding: 8px; background: #fff; border-radius: 6px; border: 1px solid #e7e7e8;
}
.link-row .drag-handle { cursor: grab; color: #a1acb8; }
.contact-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.payment-grid { display: flex; flex-wrap: wrap; gap: 10px; }
.payment-item {
    display: flex; align-items: center; gap: 8px; padding: 10px 14px;
    background: #f8f7fa; border-radius: 8px; border: 1px solid #e7e7e8;
}
.footer-preview {
    background: #1a1a2e; color: #fff; border-radius: 12px; padding: 30px; margin-top: 1rem;
}
.footer-preview h6 { color: #fff; font-size: .9rem; margin-bottom: 12px; }
.footer-preview a { color: #a1acb8; text-decoration: none; font-size: .85rem; display: block; margin-bottom: 6px; }
.footer-preview .footer-bottom { border-top: 1px solid rgba(255,255,255,.1); padding-top: 16px; margin-top: 20px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-layout-bottombar me-2"></i>Footer Settings</h4>
        <p class="text-muted mb-0">Configure the website footer content and layout</p>
    </div>
</div>

<form action="{{ route('website.footer.update') }}" method="POST" id="footerForm">
@csrf
@method('PUT')

<div class="row">
    <div class="col-lg-3 col-md-4 mb-4">
        <div class="card cms-nav">
            <div class="card-body p-3">
                <nav class="nav flex-column gap-1">
                    <a class="nav-link active" data-section="about" href="javascript:void(0)">
                        <i class="ti tabler-info-circle"></i> About Section
                    </a>
                    <a class="nav-link" data-section="columns" href="javascript:void(0)">
                        <i class="ti tabler-columns"></i> Footer Columns
                    </a>
                    <a class="nav-link" data-section="bottom-bar" href="javascript:void(0)">
                        <i class="ti tabler-line"></i> Bottom Bar
                    </a>
                    <a class="nav-link" data-section="payment" href="javascript:void(0)">
                        <i class="ti tabler-credit-card"></i> Payment Icons
                    </a>
                    <a class="nav-link" data-section="preview" href="javascript:void(0)">
                        <i class="ti tabler-eye"></i> Preview
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <div class="col-lg-9 col-md-8">

        {{-- ── About Section ── --}}
        <div class="cms-section active" id="sec-about">
            @php $about = $settings['about'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-info-circle text-primary"></i> About Section</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="showLogo"
                                       {{ ($about['show_logo'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showLogo">Show Logo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="showSocial"
                                       {{ ($about['show_social'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="showSocial">Show Social Links</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea id="aboutDescription" class="form-control" rows="3">{{ $about['description'] ?? '' }}</textarea>
                            <div class="form-hint">Brief description shown in the first footer column</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Footer Columns ── --}}
        <div class="cms-section" id="sec-columns">
            @php $columns = $settings['columns'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="ti tabler-columns text-primary"></i> Footer Columns</h6>
                    <button type="button" class="btn btn-sm btn-label-primary" id="addColumnBtn">
                        <i class="ti tabler-plus me-1"></i> Add Column
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Configure footer columns. Each column can have links or contact information.</p>
                    <div id="columnsContainer">
                        @foreach($columns as $cIdx => $column)
                        <div class="column-card" data-col-idx="{{ $cIdx }}">
                            <div class="column-header">
                                <input type="text" class="form-control form-control-sm col-title" style="max-width:250px;"
                                       value="{{ $column['title'] ?? '' }}" placeholder="Column Title">
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm col-type" style="width:120px;">
                                        <option value="links" {{ isset($column['links']) ? 'selected' : '' }}>Links</option>
                                        <option value="contact" {{ isset($column['items']) ? 'selected' : '' }}>Contact</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-col-btn">
                                        <i class="ti tabler-trash"></i>
                                    </button>
                                </div>
                            </div>

                            @if(isset($column['links']))
                                <div class="col-links">
                                    @foreach($column['links'] as $link)
                                    <div class="link-row">
                                        <i class="ti tabler-grip-vertical drag-handle"></i>
                                        <input type="text" class="form-control form-control-sm link-label" value="{{ $link['label'] ?? '' }}" placeholder="Label">
                                        <input type="text" class="form-control form-control-sm link-url" value="{{ $link['url'] ?? '' }}" placeholder="/url">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-link-btn"><i class="ti tabler-x"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-label-secondary add-link-btn mt-2">
                                    <i class="ti tabler-plus me-1"></i> Add Link
                                </button>
                            @elseif(isset($column['items']))
                                <div class="col-contacts">
                                    @foreach($column['items'] as $item)
                                    <div class="contact-row">
                                        <input type="text" class="form-control form-control-sm contact-icon" style="width:140px;" value="{{ $item['icon'] ?? '' }}" placeholder="tabler-map-pin">
                                        <input type="text" class="form-control form-control-sm contact-text flex-grow-1" value="{{ $item['text'] ?? '' }}" placeholder="Contact info">
                                        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-contact-btn"><i class="ti tabler-x"></i></button>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-sm btn-label-secondary add-contact-btn mt-2">
                                    <i class="ti tabler-plus me-1"></i> Add Item
                                </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="columns" id="columnsInput">
                </div>
            </div>
        </div>

        {{-- ── Bottom Bar ── --}}
        <div class="cms-section" id="sec-bottom-bar">
            @php $bottom = $settings['bottom_bar'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-line text-primary"></i> Bottom Bar</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Copyright Text</label>
                            <input type="text" id="copyrightText" class="form-control"
                                   value="{{ $bottom['copyright'] ?? '' }}">
                            <div class="form-hint">Use {year} to insert the current year dynamically</div>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Bottom Links</h6>
                    <div id="bottomLinksContainer">
                        @foreach(($bottom['links'] ?? []) as $link)
                        <div class="link-row">
                            <i class="ti tabler-grip-vertical drag-handle"></i>
                            <input type="text" class="form-control form-control-sm bottom-link-label" value="{{ $link['label'] ?? '' }}" placeholder="Label">
                            <input type="text" class="form-control form-control-sm bottom-link-url" value="{{ $link['url'] ?? '' }}" placeholder="/url">
                            <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-bottom-link-btn"><i class="ti tabler-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-label-secondary mt-2" id="addBottomLinkBtn">
                        <i class="ti tabler-plus me-1"></i> Add Link
                    </button>
                    <input type="hidden" name="bottom_bar" id="bottomBarInput">
                </div>
            </div>
        </div>

        {{-- ── Payment Icons ── --}}
        <div class="cms-section" id="sec-payment">
            @php $payment = $settings['payment_icons'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-credit-card text-primary"></i> Payment Method Icons</h6></div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="paymentEnabled"
                               {{ ($payment['enabled'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="paymentEnabled">Show payment method icons in footer</label>
                    </div>
                    <p class="text-muted mb-3">Select which payment methods to display:</p>
                    <div class="payment-grid">
                        @php
                            $methods = [
                                'visa' => 'Visa', 'mastercard' => 'Mastercard', 'paypal' => 'PayPal',
                                'stripe' => 'Stripe', 'apple_pay' => 'Apple Pay', 'google_pay' => 'Google Pay',
                                'amex' => 'Amex', 'discover' => 'Discover', 'bitcoin' => 'Bitcoin',
                            ];
                            $activeMethods = $payment['methods'] ?? [];
                        @endphp
                        @foreach($methods as $methodKey => $methodLabel)
                        <div class="payment-item">
                            <div class="form-check mb-0">
                                <input class="form-check-input payment-method-check" type="checkbox"
                                       value="{{ $methodKey }}" {{ in_array($methodKey, $activeMethods) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $methodLabel }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="payment_icons" id="paymentIconsInput">
                </div>
            </div>
        </div>

        {{-- ── Preview ── --}}
        <div class="cms-section" id="sec-preview">
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-eye text-primary"></i> Footer Preview</h6></div>
                <div class="card-body">
                    <div class="footer-preview" id="footerPreview">
                        <div class="row g-4">
                            <div class="col-md-3" id="previewAbout">
                                <h6>GameHub</h6>
                                <p style="font-size:.85rem;color:#a1acb8;" id="previewDesc">{{ $about['description'] ?? '' }}</p>
                            </div>
                            <div id="previewColumns" class="col-md-9">
                                <div class="row g-4">
                                    @foreach($columns as $col)
                                    <div class="col-md-4">
                                        <h6>{{ $col['title'] ?? '' }}</h6>
                                        @if(isset($col['links']))
                                            @foreach($col['links'] as $link)
                                                <a href="#">{{ $link['label'] ?? '' }}</a>
                                            @endforeach
                                        @elseif(isset($col['items']))
                                            @foreach($col['items'] as $item)
                                                <p style="font-size:.85rem;color:#a1acb8;margin-bottom:6px;">{{ $item['text'] ?? '' }}</p>
                                            @endforeach
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="footer-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <small style="color:#a1acb8;" id="previewCopyright">{{ str_replace('{year}', date('Y'), $bottom['copyright'] ?? '') }}</small>
                            <div id="previewBottomLinks">
                                @foreach(($bottom['links'] ?? []) as $link)
                                    <a href="#" class="me-3">{{ $link['label'] ?? '' }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="ti tabler-device-floppy me-1"></i> Save Changes
            </button>
        </div>

    </div>
</div>

<input type="hidden" name="about" id="aboutInput">
</form>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section navigation
    document.querySelectorAll('.cms-nav .nav-link').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.cms-nav .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.cms-section').forEach(s => s.classList.remove('active'));
            document.getElementById('sec-' + this.dataset.section)?.classList.add('active');
        });
    });

    const columnsContainer = document.getElementById('columnsContainer');

    // Add column
    document.getElementById('addColumnBtn')?.addEventListener('click', function() {
        const idx = columnsContainer.children.length;
        const html = `
            <div class="column-card" data-col-idx="${idx}">
                <div class="column-header">
                    <input type="text" class="form-control form-control-sm col-title" style="max-width:250px;" placeholder="Column Title">
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm col-type" style="width:120px;">
                            <option value="links">Links</option>
                            <option value="contact">Contact</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-col-btn"><i class="ti tabler-trash"></i></button>
                    </div>
                </div>
                <div class="col-links"></div>
                <button type="button" class="btn btn-sm btn-label-secondary add-link-btn mt-2"><i class="ti tabler-plus me-1"></i> Add Link</button>
            </div>`;
        columnsContainer.insertAdjacentHTML('beforeend', html);
    });

    // Delegate events
    columnsContainer?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-col-btn')) {
            e.target.closest('.column-card').remove();
        }
        if (e.target.closest('.add-link-btn')) {
            const card = e.target.closest('.column-card');
            const container = card.querySelector('.col-links');
            if (container) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="link-row">
                        <i class="ti tabler-grip-vertical drag-handle"></i>
                        <input type="text" class="form-control form-control-sm link-label" placeholder="Label">
                        <input type="text" class="form-control form-control-sm link-url" placeholder="/url">
                        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-link-btn"><i class="ti tabler-x"></i></button>
                    </div>`);
            }
        }
        if (e.target.closest('.add-contact-btn')) {
            const card = e.target.closest('.column-card');
            const container = card.querySelector('.col-contacts');
            if (container) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="contact-row">
                        <input type="text" class="form-control form-control-sm contact-icon" style="width:140px;" placeholder="tabler-map-pin">
                        <input type="text" class="form-control form-control-sm contact-text flex-grow-1" placeholder="Contact info">
                        <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-contact-btn"><i class="ti tabler-x"></i></button>
                    </div>`);
            }
        }
        if (e.target.closest('.remove-link-btn')) {
            e.target.closest('.link-row').remove();
        }
        if (e.target.closest('.remove-contact-btn')) {
            e.target.closest('.contact-row').remove();
        }
    });

    // Column type change
    columnsContainer?.addEventListener('change', function(e) {
        if (e.target.classList.contains('col-type')) {
            const card = e.target.closest('.column-card');
            const type = e.target.value;
            const existingLinks = card.querySelector('.col-links');
            const existingContacts = card.querySelector('.col-contacts');
            const existingAddLink = card.querySelector('.add-link-btn');
            const existingAddContact = card.querySelector('.add-contact-btn');

            if (existingLinks) existingLinks.remove();
            if (existingContacts) existingContacts.remove();
            if (existingAddLink) existingAddLink.remove();
            if (existingAddContact) existingAddContact.remove();

            if (type === 'links') {
                card.insertAdjacentHTML('beforeend', `
                    <div class="col-links"></div>
                    <button type="button" class="btn btn-sm btn-label-secondary add-link-btn mt-2"><i class="ti tabler-plus me-1"></i> Add Link</button>`);
            } else {
                card.insertAdjacentHTML('beforeend', `
                    <div class="col-contacts"></div>
                    <button type="button" class="btn btn-sm btn-label-secondary add-contact-btn mt-2"><i class="ti tabler-plus me-1"></i> Add Item</button>`);
            }
        }
    });

    // Add bottom link
    document.getElementById('addBottomLinkBtn')?.addEventListener('click', function() {
        document.getElementById('bottomLinksContainer').insertAdjacentHTML('beforeend', `
            <div class="link-row">
                <i class="ti tabler-grip-vertical drag-handle"></i>
                <input type="text" class="form-control form-control-sm bottom-link-label" placeholder="Label">
                <input type="text" class="form-control form-control-sm bottom-link-url" placeholder="/url">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-bottom-link-btn"><i class="ti tabler-x"></i></button>
            </div>`);
    });

    document.getElementById('bottomLinksContainer')?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-bottom-link-btn')) {
            e.target.closest('.link-row').remove();
        }
    });

    // Before submit: collect all data
    document.getElementById('footerForm').addEventListener('submit', function() {
        // About
        document.getElementById('aboutInput').value = JSON.stringify({
            show_logo: document.getElementById('showLogo').checked,
            description: document.getElementById('aboutDescription').value,
            show_social: document.getElementById('showSocial').checked,
        });

        // Columns
        const cols = [];
        columnsContainer.querySelectorAll('.column-card').forEach(card => {
            const title = card.querySelector('.col-title').value;
            const type = card.querySelector('.col-type').value;
            const col = { title };

            if (type === 'links') {
                col.links = [];
                card.querySelectorAll('.link-row').forEach(row => {
                    col.links.push({
                        label: row.querySelector('.link-label').value,
                        url: row.querySelector('.link-url').value,
                    });
                });
            } else {
                col.items = [];
                card.querySelectorAll('.contact-row').forEach(row => {
                    col.items.push({
                        icon: row.querySelector('.contact-icon').value,
                        text: row.querySelector('.contact-text').value,
                    });
                });
            }
            cols.push(col);
        });
        document.getElementById('columnsInput').value = JSON.stringify(cols);

        // Bottom bar
        const bottomLinks = [];
        document.querySelectorAll('#bottomLinksContainer .link-row').forEach(row => {
            bottomLinks.push({
                label: row.querySelector('.bottom-link-label').value,
                url: row.querySelector('.bottom-link-url').value,
            });
        });
        document.getElementById('bottomBarInput').value = JSON.stringify({
            copyright: document.getElementById('copyrightText').value,
            links: bottomLinks,
        });

        // Payment icons
        const paymentMethods = [];
        document.querySelectorAll('.payment-method-check:checked').forEach(cb => {
            paymentMethods.push(cb.value);
        });
        document.getElementById('paymentIconsInput').value = JSON.stringify({
            enabled: document.getElementById('paymentEnabled').checked,
            methods: paymentMethods,
        });
    });
});
</script>
@endpush
