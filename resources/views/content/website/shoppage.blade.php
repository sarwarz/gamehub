@extends('layouts.app')

@section('title', 'Shop Page Settings')

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
.filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
.filter-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 16px; background: #f8f7fa; border-radius: 8px; border: 1px solid #e7e7e8;
}
.filter-item .form-check-label { font-weight: 500; }
.preview-card { background: #f8f7fa; border-radius: 10px; padding: 20px; }
.preview-card .preview-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.preview-grid { display: grid; gap: 12px; }
.preview-product { background: #fff; border-radius: 8px; padding: 12px; height: 100px; border: 1px solid #e7e7e8; }
.img-upload-zone {
    border: 2px dashed #d9dee3; border-radius: 10px; padding: 1.25rem;
    text-align: center; cursor: pointer; transition: border-color .2s;
    min-height: 120px; display: flex; align-items: center; justify-content: center;
}
.img-upload-zone:hover { border-color: #7367f0; }
.img-upload-zone img { max-height: 100px; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-shopping-cart me-2"></i>Shop Page Settings</h4>
        <p class="text-muted mb-0">Configure the product listing and shop page layout</p>
    </div>
</div>

<form action="{{ route('website.shoppage.update') }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">
    <div class="col-lg-3 col-md-4 mb-4">
        <div class="card cms-nav">
            <div class="card-body p-3">
                <nav class="nav flex-column gap-1">
                    <a class="nav-link active" data-section="layout" href="javascript:void(0)">
                        <i class="ti tabler-layout"></i> Layout
                    </a>
                    <a class="nav-link" data-section="filters" href="javascript:void(0)">
                        <i class="ti tabler-filter"></i> Filters
                    </a>
                    <a class="nav-link" data-section="banner" href="javascript:void(0)">
                        <i class="ti tabler-photo"></i> Banner
                    </a>
                    <a class="nav-link" data-section="seo" href="javascript:void(0)">
                        <i class="ti tabler-seo"></i> SEO
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <div class="col-lg-9 col-md-8">

        {{-- ── Layout ── --}}
        <div class="cms-section active" id="sec-layout">
            @php $layout = $settings['layout'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-layout text-primary"></i> Layout Settings</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Default View</label>
                            <select name="layout[default_view]" class="form-select" id="defaultView">
                                <option value="grid" {{ ($layout['default_view'] ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid View</option>
                                <option value="list" {{ ($layout['default_view'] ?? '') === 'list' ? 'selected' : '' }}>List View</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Products Per Page</label>
                            <select name="layout[products_per_page]" class="form-select">
                                @foreach([8, 12, 16, 20, 24, 32] as $pp)
                                    <option value="{{ $pp }}" {{ ($layout['products_per_page'] ?? 12) == $pp ? 'selected' : '' }}>{{ $pp }} products</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Sort</label>
                            <select name="layout[default_sort]" class="form-select">
                                <option value="featured" {{ ($layout['default_sort'] ?? 'featured') === 'featured' ? 'selected' : '' }}>Featured</option>
                                <option value="newest" {{ ($layout['default_sort'] ?? '') === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_low" {{ ($layout['default_sort'] ?? '') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ ($layout['default_sort'] ?? '') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="bestselling" {{ ($layout['default_sort'] ?? '') === 'bestselling' ? 'selected' : '' }}>Best Selling</option>
                                <option value="rating" {{ ($layout['default_sort'] ?? '') === 'rating' ? 'selected' : '' }}>Top Rated</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sidebar Position</label>
                            <select name="layout[sidebar_position]" class="form-select">
                                <option value="left" {{ ($layout['sidebar_position'] ?? 'left') === 'left' ? 'selected' : '' }}>Left</option>
                                <option value="right" {{ ($layout['sidebar_position'] ?? '') === 'right' ? 'selected' : '' }}>Right</option>
                                <option value="none" {{ ($layout['sidebar_position'] ?? '') === 'none' ? 'selected' : '' }}>No Sidebar</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grid Columns</label>
                            <select name="layout[columns]" class="form-select" id="gridColumns">
                                @foreach([2, 3, 4, 5, 6] as $col)
                                    <option value="{{ $col }}" {{ ($layout['columns'] ?? 4) == $col ? 'selected' : '' }}>{{ $col }} Columns</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Layout Preview</h6>
                    <div class="preview-card">
                        <div class="preview-header">
                            <span class="text-muted">Showing products in <strong id="previewViewLabel">Grid</strong> view</span>
                            <span class="badge bg-label-primary" id="previewColLabel">4 columns</span>
                        </div>
                        <div class="preview-grid" id="previewGrid" style="grid-template-columns: repeat({{ $layout['columns'] ?? 4 }}, 1fr);">
                            @for($i = 0; $i < ($layout['columns'] ?? 4); $i++)
                                <div class="preview-product"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Filters ── --}}
        <div class="cms-section" id="sec-filters">
            @php $filters = $settings['filters'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-filter text-primary"></i> Sidebar Filters</h6></div>
                <div class="card-body">
                    <p class="text-muted mb-3">Enable or disable filter sections on the shop sidebar.</p>
                    <div class="filter-grid">
                        @php
                            $filterOptions = [
                                'price_range' => ['label' => 'Price Range', 'icon' => 'tabler-currency-dollar'],
                                'categories'  => ['label' => 'Categories', 'icon' => 'tabler-category'],
                                'platforms'   => ['label' => 'Platforms', 'icon' => 'tabler-device-gamepad-2'],
                                'types'       => ['label' => 'Types', 'icon' => 'tabler-tags'],
                                'regions'     => ['label' => 'Regions', 'icon' => 'tabler-world'],
                                'languages'   => ['label' => 'Languages', 'icon' => 'tabler-language'],
                                'works_on'    => ['label' => 'Works On', 'icon' => 'tabler-device-desktop'],
                                'developers'  => ['label' => 'Developers', 'icon' => 'tabler-code'],
                                'publishers'  => ['label' => 'Publishers', 'icon' => 'tabler-building'],
                            ];
                        @endphp
                        @foreach($filterOptions as $filterKey => $filterInfo)
                            <div class="filter-item">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti {{ $filterInfo['icon'] }} text-primary"></i>
                                    <label class="form-check-label">{{ $filterInfo['label'] }}</label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="filters[{{ $filterKey }}]"
                                           value="1" {{ ($filters[$filterKey] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Banner ── --}}
        <div class="cms-section" id="sec-banner">
            @php $banner = $settings['banner'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-photo text-primary"></i> Shop Banner</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="banner[enabled]"
                                       value="1" {{ ($banner['enabled'] ?? false) ? 'checked' : '' }} id="bannerEnabled">
                                <label class="form-check-label" for="bannerEnabled">Show banner at the top of shop page</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="banner[title]" class="form-control"
                                   value="{{ $banner['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link URL</label>
                            <input type="text" name="banner[url]" class="form-control"
                                   value="{{ $banner['url'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner Image</label>
                            <div class="img-upload-zone" onclick="document.getElementById('shopBannerImg').click()">
                                @if(!empty($banner['image']))
                                    <img src="{{ $banner['image'] }}" alt="Banner">
                                @else
                                    <div><i class="ti tabler-cloud-upload fs-3 text-muted"></i><br><small class="text-muted">Click to upload</small></div>
                                @endif
                            </div>
                            <input type="file" id="shopBannerImg" name="banner_image" accept="image/*" class="d-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SEO ── --}}
        <div class="cms-section" id="sec-seo">
            @php $seo = $settings['seo'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-seo text-primary"></i> SEO Settings</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Page Title</label>
                            <input type="text" name="seo[title]" class="form-control"
                                   value="{{ $seo['title'] ?? 'All Products' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="seo[keywords]" class="form-control"
                                   value="{{ $seo['keywords'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="seo[description]" class="form-control" rows="3">{{ $seo['description'] ?? '' }}</textarea>
                            <div class="form-hint">Recommended: 150-160 characters</div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3">Search Preview</h6>
                    <div class="preview-card">
                        <div style="color:#1a0dab; font-size:18px;" id="seoPreviewTitle">{{ $seo['title'] ?? 'All Products' }} - GameHub</div>
                        <div style="color:#006621; font-size:14px;">https://gamehub.com/shop</div>
                        <div style="color:#545454; font-size:13px;" id="seoPreviewDesc">{{ $seo['description'] ?? '' }}</div>
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

    // Layout preview
    const viewSelect = document.getElementById('defaultView');
    const colSelect = document.getElementById('gridColumns');
    const grid = document.getElementById('previewGrid');

    function updatePreview() {
        const cols = colSelect?.value || 4;
        const view = viewSelect?.value || 'grid';
        document.getElementById('previewViewLabel').textContent = view === 'grid' ? 'Grid' : 'List';
        document.getElementById('previewColLabel').textContent = cols + ' columns';

        if (view === 'list') {
            grid.style.gridTemplateColumns = '1fr';
            grid.innerHTML = '';
            for (let i = 0; i < 3; i++) grid.innerHTML += '<div class="preview-product" style="height:60px;"></div>';
        } else {
            grid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
            grid.innerHTML = '';
            for (let i = 0; i < parseInt(cols); i++) grid.innerHTML += '<div class="preview-product"></div>';
        }
    }

    viewSelect?.addEventListener('change', updatePreview);
    colSelect?.addEventListener('change', updatePreview);

    // SEO preview
    document.querySelector('input[name="seo[title]"]')?.addEventListener('input', function() {
        document.getElementById('seoPreviewTitle').textContent = this.value + ' - GameHub';
    });
    document.querySelector('textarea[name="seo[description]"]')?.addEventListener('input', function() {
        document.getElementById('seoPreviewDesc').textContent = this.value;
    });

    // Image preview
    document.getElementById('shopBannerImg')?.addEventListener('change', function() {
        if (this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                const zone = this.closest('.col-md-6').querySelector('.img-upload-zone');
                if (zone) zone.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
@endpush
