@extends('layouts.app')
@section('title', 'Website / CMS Settings')
@include('content.settings.partials.settings-layout')

@push('page-css')
<style>
.cms-tabs .nav-link {
    font-weight: 600;
    color: #566a7f;
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem 0.5rem 0 0;
}
.cms-tabs .nav-link.active {
    color: #7367f0;
    background: #fff;
    border-bottom: 2px solid #7367f0;
}
.cms-tabs .nav-link:hover:not(.active) {
    color: #7367f0;
    background: rgba(115, 103, 240, 0.04);
}
.section-card {
    border: 1px solid #f0f0f3;
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    transition: box-shadow 0.2s;
}
.section-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.section-card:last-child {
    margin-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-layout"></i></div>
            <div>
                <h4>Website / CMS</h4>
                <p>Configure homepage sections, shop page layout, and footer</p>
            </div>
        </div>

        <ul class="nav nav-tabs cms-tabs mb-0" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-homepage"><i class="ti tabler-home me-1"></i> Homepage</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-shoppage"><i class="ti tabler-building-store me-1"></i> Shop Page</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-footer"><i class="ti tabler-layout-bottombar me-1"></i> Footer</a></li>
        </ul>

        <div class="tab-content">
            {{-- ==================== HOMEPAGE TAB ==================== --}}
            <div class="tab-pane fade show active" id="tab-homepage">
                <form id="homepageForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card" style="border-radius: 0 0 0.75rem 0.75rem;">
                        <div class="card-header">
                            <h5><i class="ti tabler-layout-dashboard text-primary me-2"></i>Homepage Sections</h5>
                            <p>Toggle and configure which sections appear on your homepage</p>
                        </div>
                        <div class="card-body">
                            @php
                                $sections = [
                                    'hero_slider'        => ['label' => 'Hero Slider',          'desc' => 'Main banner slider at the top of the page'],
                                    'category_bar'       => ['label' => 'Category Bar',         'desc' => 'Horizontal category navigation bar'],
                                    'featured_products'  => ['label' => 'Featured Products',    'desc' => 'Showcase hand-picked featured products', 'has_limit' => true],
                                    'promotional_banner' => ['label' => 'Promotional Banner',   'desc' => 'Full-width promotional banner area'],
                                    'new_arrivals'       => ['label' => 'New Arrivals',         'desc' => 'Recently added products section', 'has_limit' => true],
                                    'categories_grid'    => ['label' => 'Categories Grid',      'desc' => 'Visual grid of product categories'],
                                    'stats_counter'      => ['label' => 'Stats Counter',        'desc' => 'Animated statistics counters'],
                                    'hot_deals'          => ['label' => 'Hot Deals',            'desc' => 'Time-limited deals and discounts', 'has_limit' => true],
                                    'blog_section'       => ['label' => 'Blog Section',         'desc' => 'Latest blog posts preview', 'has_limit' => true],
                                    'newsletter'         => ['label' => 'Newsletter',           'desc' => 'Email subscription signup section'],
                                ];
                            @endphp

                            @foreach($sections as $key => $section)
                            <div class="section-card">
                                <div class="setting-toggle">
                                    <div class="setting-toggle-info">
                                        <h6>{{ $section['label'] }}</h6>
                                        <p>{{ $section['desc'] }}</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="homepage[{{ $key }}][enabled]" value="1" {{ ($homepage[$key]['enabled'] ?? false) ? 'checked' : '' }}>
                                    </div>
                                </div>
                                @if(!empty($section['has_limit']))
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Limit</label>
                                        <input type="number" name="homepage[{{ $key }}][limit]" class="form-control form-control-sm" value="{{ $homepage[$key]['limit'] ?? 8 }}" min="1" max="50">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="homepage[{{ $key }}][title]" class="form-control form-control-sm" value="{{ $homepage[$key]['title'] ?? '' }}" placeholder="{{ $section['label'] }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Subtitle</label>
                                        <input type="text" name="homepage[{{ $key }}][subtitle]" class="form-control form-control-sm" value="{{ $homepage[$key]['subtitle'] ?? '' }}" placeholder="Optional subtitle">
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- ==================== SHOP PAGE TAB ==================== --}}
            <div class="tab-pane fade" id="tab-shoppage">
                <form id="shoppageForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card" style="border-radius: 0 0 0.75rem 0.75rem;">
                        <div class="card-header">
                            <h5><i class="ti tabler-layout-grid text-primary me-2"></i>Layout</h5>
                        </div>
                        <div class="card-body row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Default View</label>
                                <select name="shoppage[default_view]" class="form-select">
                                    <option value="grid" {{ ($shoppage['default_view'] ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid</option>
                                    <option value="list" {{ ($shoppage['default_view'] ?? '') === 'list' ? 'selected' : '' }}>List</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Products Per Page</label>
                                <input type="number" name="shoppage[products_per_page]" class="form-control" value="{{ $shoppage['products_per_page'] ?? 12 }}" min="1" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Sort</label>
                                <select name="shoppage[default_sort]" class="form-select">
                                    @foreach([
                                        'newest'     => 'Newest First',
                                        'price_asc'  => 'Price: Low to High',
                                        'price_desc' => 'Price: High to Low',
                                        'popular'    => 'Most Popular',
                                        'rating'     => 'Highest Rated',
                                    ] as $val => $label)
                                    <option value="{{ $val }}" {{ ($shoppage['default_sort'] ?? 'newest') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sidebar Position</label>
                                <select name="shoppage[sidebar_position]" class="form-select">
                                    <option value="left" {{ ($shoppage['sidebar_position'] ?? 'left') === 'left' ? 'selected' : '' }}>Left</option>
                                    <option value="right" {{ ($shoppage['sidebar_position'] ?? '') === 'right' ? 'selected' : '' }}>Right</option>
                                    <option value="none" {{ ($shoppage['sidebar_position'] ?? '') === 'none' ? 'selected' : '' }}>None (No Sidebar)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-filter text-primary me-2"></i>Filters</h5>
                            <p>Control which filters are available on the shop page</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'price_range' => ['label' => 'Price Range Filter',   'desc' => 'Allow customers to filter products by price range'],
                                'categories'  => ['label' => 'Categories Filter',    'desc' => 'Show category filter in the sidebar'],
                                'platforms'   => ['label' => 'Platforms Filter',     'desc' => 'Filter products by gaming platform'],
                                'regions'     => ['label' => 'Regions Filter',       'desc' => 'Filter products by region availability'],
                                'ratings'     => ['label' => 'Ratings Filter',       'desc' => 'Filter products by customer ratings'],
                            ] as $filterKey => $filter)
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>{{ $filter['label'] }}</h6>
                                    <p>{{ $filter['desc'] }}</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="shoppage[filters][{{ $filterKey }}]" value="1" {{ ($shoppage['filters'][$filterKey] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-ad text-primary me-2"></i>Banner</h5>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle mb-3">
                                <div class="setting-toggle-info">
                                    <h6>Show Banner</h6>
                                    <p>Display a promotional banner at the top of the shop page</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="shoppage[banner][enabled]" value="1" {{ ($shoppage['banner']['enabled'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">Banner Title</label>
                                    <input type="text" name="shoppage[banner][title]" class="form-control" value="{{ $shoppage['banner']['title'] ?? '' }}" placeholder="Shop our latest deals">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Banner URL</label>
                                    <input type="text" name="shoppage[banner][url]" class="form-control" value="{{ $shoppage['banner']['url'] ?? '' }}" placeholder="/promotions">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-search text-primary me-2"></i>Shop Page SEO</h5>
                        </div>
                        <div class="card-body row g-4">
                            <div class="col-12">
                                <label class="form-label">Page Title</label>
                                <input type="text" name="shoppage[seo_title]" class="form-control" value="{{ $shoppage['seo_title'] ?? '' }}" placeholder="Shop — Browse All Products">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Meta Description</label>
                                <textarea name="shoppage[seo_description]" class="form-control" rows="2" placeholder="Browse our full catalog of digital game keys...">{{ $shoppage['seo_description'] ?? '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Meta Keywords</label>
                                <input type="text" name="shoppage[seo_keywords]" class="form-control" value="{{ $shoppage['seo_keywords'] ?? '' }}" placeholder="shop, buy games, game keys">
                            </div>
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- ==================== FOOTER TAB ==================== --}}
            <div class="tab-pane fade" id="tab-footer">
                <form id="footerForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card" style="border-radius: 0 0 0.75rem 0.75rem;">
                        <div class="card-header">
                            <h5><i class="ti tabler-info-circle text-primary me-2"></i>About Section</h5>
                            <p>Footer about column content</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle mb-3">
                                <div class="setting-toggle-info">
                                    <h6>Show Logo</h6>
                                    <p>Display the site logo in the footer about section</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="footer[show_logo]" value="1" {{ ($footer['show_logo'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Description</label>
                                <textarea name="footer[description]" class="form-control" rows="3" placeholder="A short description about your platform...">{{ $footer['description'] ?? '' }}</textarea>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Show Social Icons</h6>
                                    <p>Display social media icons in the footer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="footer[show_social]" value="1" {{ ($footer['show_social'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-copyright text-primary me-2"></i>Bottom Bar</h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Copyright Text</label>
                            <input type="text" name="footer[copyright]" class="form-control" value="{{ $footer['copyright'] ?? '' }}" placeholder="© {{ date('Y') }} YourSite. All rights reserved.">
                            <div class="form-label-description">Displayed at the very bottom of every page</div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-credit-card text-primary me-2"></i>Payment Icons</h5>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Show Payment Icons</h6>
                                    <p>Display accepted payment method icons in the footer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="footer[payment_icons_enabled]" value="1" {{ ($footer['payment_icons_enabled'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
saveSettings('homepageForm', '{{ route("settings.website.update", "homepage") }}');
saveSettings('shoppageForm', '{{ route("settings.website.update", "shoppage") }}');
saveSettings('footerForm',   '{{ route("settings.website.update", "footer") }}');
</script>
@endpush
