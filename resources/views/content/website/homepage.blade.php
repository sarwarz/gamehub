@extends('layouts.app')

@section('title', 'Home Page Settings')

@push('page-css')
<style>
.cms-nav { position: sticky; top: 80px; }
.cms-nav .nav-link {
    display: flex; align-items: center; gap: 10px; padding: 10px 16px;
    border-radius: 8px; color: #566a7f; font-weight: 500; font-size: .9rem;
    transition: background .2s;
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
.section-toggle { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #f8f7fa; border-radius: 8px; margin-bottom: 1rem; }
.section-toggle .badge { font-size: .7rem; }
.drag-handle { cursor: grab; color: #a1acb8; }
.drag-handle:hover { color: #7367f0; }
.order-list .order-item {
    display: flex; align-items: center; gap: 12px; padding: 10px 16px;
    background: #fff; border: 1px solid #e7e7e8; border-radius: 8px; margin-bottom: 8px;
    transition: box-shadow .2s;
}
.order-list .order-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.stat-item { border: 1px solid #e7e7e8; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
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
        <h4 class="mb-1"><i class="ti tabler-home me-2"></i>Home Page Settings</h4>
        <p class="text-muted mb-0">Configure sections displayed on the homepage</p>
    </div>
</div>

<form action="{{ route('website.homepage.update') }}" method="POST" enctype="multipart/form-data" id="homepageForm">
@csrf
@method('PUT')

<div class="row">
    {{-- Sidebar Navigation --}}
    <div class="col-lg-3 col-md-4 mb-4">
        <div class="card cms-nav">
            <div class="card-body p-3">
                <nav class="nav flex-column gap-1">
                    <a class="nav-link active" data-section="sections-order" href="javascript:void(0)">
                        <i class="ti tabler-list-numbers"></i> Sections Order
                    </a>
                    <a class="nav-link" data-section="hero-slider" href="javascript:void(0)">
                        <i class="ti tabler-photo"></i> Hero Slider
                    </a>
                    <a class="nav-link" data-section="category-bar" href="javascript:void(0)">
                        <i class="ti tabler-category"></i> Category Bar
                    </a>
                    <a class="nav-link" data-section="featured-products" href="javascript:void(0)">
                        <i class="ti tabler-star"></i> Featured Products
                    </a>
                    <a class="nav-link" data-section="promotional-banner" href="javascript:void(0)">
                        <i class="ti tabler-speakerphone"></i> Promo Banner
                    </a>
                    <a class="nav-link" data-section="new-arrivals" href="javascript:void(0)">
                        <i class="ti tabler-sparkles"></i> New Arrivals
                    </a>
                    <a class="nav-link" data-section="categories-grid" href="javascript:void(0)">
                        <i class="ti tabler-layout-grid"></i> Categories Grid
                    </a>
                    <a class="nav-link" data-section="stats-counter" href="javascript:void(0)">
                        <i class="ti tabler-chart-bar"></i> Stats Counter
                    </a>
                    <a class="nav-link" data-section="hot-deals" href="javascript:void(0)">
                        <i class="ti tabler-flame"></i> Hot Deals
                    </a>
                    <a class="nav-link" data-section="blog-section" href="javascript:void(0)">
                        <i class="ti tabler-article"></i> Blog Section
                    </a>
                    <a class="nav-link" data-section="newsletter" href="javascript:void(0)">
                        <i class="ti tabler-mail"></i> Newsletter
                    </a>
                </nav>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="col-lg-9 col-md-8">

        {{-- ── Sections Order ── --}}
        <div class="cms-section active" id="sec-sections-order">
            <div class="card section-card">
                <div class="card-header">
                    <h6><i class="ti tabler-list-numbers text-primary"></i> Sections Order & Visibility</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Drag to reorder sections. Toggle switches to enable/disable.</p>
                    <div class="order-list" id="sectionsOrderList">
                        @php
                            $order = $settings['sections_order'] ?? ['hero_slider','category_bar','featured_products','promotional_banner','new_arrivals','categories_grid','stats_counter','hot_deals','blog_section','newsletter'];
                            $sectionLabels = [
                                'hero_slider' => ['icon' => 'tabler-photo', 'label' => 'Hero Slider'],
                                'category_bar' => ['icon' => 'tabler-category', 'label' => 'Category Bar'],
                                'featured_products' => ['icon' => 'tabler-star', 'label' => 'Featured Products'],
                                'promotional_banner' => ['icon' => 'tabler-speakerphone', 'label' => 'Promotional Banner'],
                                'new_arrivals' => ['icon' => 'tabler-sparkles', 'label' => 'New Arrivals'],
                                'categories_grid' => ['icon' => 'tabler-layout-grid', 'label' => 'Categories Grid'],
                                'stats_counter' => ['icon' => 'tabler-chart-bar', 'label' => 'Stats Counter'],
                                'hot_deals' => ['icon' => 'tabler-flame', 'label' => 'Hot Deals'],
                                'blog_section' => ['icon' => 'tabler-article', 'label' => 'Blog Section'],
                                'newsletter' => ['icon' => 'tabler-mail', 'label' => 'Newsletter'],
                            ];
                        @endphp
                        @foreach($order as $idx => $sectionKey)
                            @php $sec = $sectionLabels[$sectionKey] ?? ['icon' => 'tabler-box', 'label' => $sectionKey]; @endphp
                            <div class="order-item" data-section="{{ $sectionKey }}">
                                <i class="ti tabler-grip-vertical drag-handle"></i>
                                <i class="ti {{ $sec['icon'] }} text-primary"></i>
                                <span class="fw-medium flex-grow-1">{{ $sec['label'] }}</span>
                                @php $enabled = ($settings[$sectionKey] ?? [])['enabled'] ?? true; @endphp
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input section-enabled-toggle" type="checkbox"
                                           data-key="{{ $sectionKey }}" {{ $enabled ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="sections_order" id="sectionsOrderInput" value='@json($order)'>
                </div>
            </div>
        </div>

        {{-- ── Hero Slider ── --}}
        <div class="cms-section" id="sec-hero-slider">
            @php $hero = $settings['hero_slider'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="ti tabler-photo text-primary"></i> Hero Slider</h6>
                    <a href="{{ route('sliders.index') }}" class="btn btn-sm btn-label-primary">
                        <i class="ti tabler-external-link me-1"></i> Manage Sliders
                    </a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">The hero slider is managed from the Sliders page. Configure display options here.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Autoplay</label>
                            <select name="hero_slider[autoplay]" class="form-select">
                                <option value="1" {{ ($hero['autoplay'] ?? true) ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !($hero['autoplay'] ?? true) ? 'selected' : '' }}>Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slide Speed (ms)</label>
                            <input type="number" name="hero_slider[speed]" class="form-control"
                                   value="{{ $hero['speed'] ?? 5000 }}" min="1000" max="15000" step="500">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slider Type</label>
                            <select name="hero_slider[type]" class="form-select">
                                <option value="hero" {{ ($hero['type'] ?? 'hero') === 'hero' ? 'selected' : '' }}>Hero (Full Width)</option>
                                <option value="banner" {{ ($hero['type'] ?? '') === 'banner' ? 'selected' : '' }}>Banner</option>
                            </select>
                        </div>
                        <input type="hidden" name="hero_slider[enabled]" value="{{ ($hero['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Category Bar ── --}}
        <div class="cms-section" id="sec-category-bar">
            @php $catBar = $settings['category_bar'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-category text-primary"></i> Category Bar</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Number of Categories</label>
                            <input type="number" name="category_bar[limit]" class="form-control"
                                   value="{{ $catBar['limit'] ?? 6 }}" min="2" max="12">
                            <div class="form-hint">How many top-level categories to show</div>
                        </div>
                        <input type="hidden" name="category_bar[enabled]" value="{{ ($catBar['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Featured Products ── --}}
        <div class="cms-section" id="sec-featured-products">
            @php $feat = $settings['featured_products'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-star text-primary"></i> Featured Products</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="featured_products[title]" class="form-control"
                                   value="{{ $feat['title'] ?? 'Featured Products' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="featured_products[subtitle]" class="form-control"
                                   value="{{ $feat['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Products to Show</label>
                            <input type="number" name="featured_products[limit]" class="form-control"
                                   value="{{ $feat['limit'] ?? 8 }}" min="2" max="20">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort By</label>
                            <select name="featured_products[sort]" class="form-select">
                                <option value="featured" {{ ($feat['sort'] ?? '') === 'featured' ? 'selected' : '' }}>Featured</option>
                                <option value="bestselling" {{ ($feat['sort'] ?? '') === 'bestselling' ? 'selected' : '' }}>Best Selling</option>
                                <option value="rating" {{ ($feat['sort'] ?? '') === 'rating' ? 'selected' : '' }}>Top Rated</option>
                                <option value="newest" {{ ($feat['sort'] ?? '') === 'newest' ? 'selected' : '' }}>Newest</option>
                            </select>
                        </div>
                        <input type="hidden" name="featured_products[enabled]" value="{{ ($feat['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Promotional Banner ── --}}
        <div class="cms-section" id="sec-promotional-banner">
            @php $promo = $settings['promotional_banner'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-speakerphone text-primary"></i> Promotional Banner</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Badge Text</label>
                            <input type="text" name="promotional_banner[badge_text]" class="form-control"
                                   value="{{ $promo['badge_text'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="promotional_banner[title]" class="form-control"
                                   value="{{ $promo['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="promotional_banner[subtitle]" class="form-control"
                                   value="{{ $promo['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Background Color</label>
                            <input type="color" name="promotional_banner[bg_color]" class="form-control form-control-color"
                                   value="{{ $promo['bg_color'] ?? '#1a1a2e' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="promotional_banner[description]" class="form-control" rows="2">{{ $promo['description'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Video URL</label>
                            <input type="url" name="promotional_banner[video_url]" class="form-control"
                                   value="{{ $promo['video_url'] ?? '' }}" placeholder="https://youtube.com/...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner Image</label>
                            <div class="img-upload-zone" onclick="document.getElementById('promoBannerImg').click()">
                                @if(!empty($promo['image']))
                                    <img src="{{ $promo['image'] }}" alt="Banner">
                                @else
                                    <div><i class="ti tabler-cloud-upload fs-3 text-muted"></i><br><small class="text-muted">Click to upload</small></div>
                                @endif
                            </div>
                            <input type="file" id="promoBannerImg" name="promotional_banner_image" accept="image/*" class="d-none">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Button 1 Text</label>
                            <input type="text" name="promotional_banner[button_text]" class="form-control"
                                   value="{{ $promo['button_text'] ?? 'Shop Now' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Button 1 URL</label>
                            <input type="text" name="promotional_banner[button_url]" class="form-control"
                                   value="{{ $promo['button_url'] ?? '/shop' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Button 2 Text</label>
                            <input type="text" name="promotional_banner[button2_text]" class="form-control"
                                   value="{{ $promo['button2_text'] ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Button 2 URL</label>
                            <input type="text" name="promotional_banner[button2_url]" class="form-control"
                                   value="{{ $promo['button2_url'] ?? '' }}">
                        </div>
                        <input type="hidden" name="promotional_banner[enabled]" value="{{ ($promo['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── New Arrivals ── --}}
        <div class="cms-section" id="sec-new-arrivals">
            @php $newArr = $settings['new_arrivals'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-sparkles text-primary"></i> New Arrivals</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="new_arrivals[title]" class="form-control"
                                   value="{{ $newArr['title'] ?? 'New Arrivals' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="new_arrivals[subtitle]" class="form-control"
                                   value="{{ $newArr['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Products to Show</label>
                            <input type="number" name="new_arrivals[limit]" class="form-control"
                                   value="{{ $newArr['limit'] ?? 8 }}" min="2" max="20">
                        </div>
                        <input type="hidden" name="new_arrivals[enabled]" value="{{ ($newArr['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Categories Grid ── --}}
        <div class="cms-section" id="sec-categories-grid">
            @php $catGrid = $settings['categories_grid'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-layout-grid text-primary"></i> Categories Grid</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="categories_grid[title]" class="form-control"
                                   value="{{ $catGrid['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="categories_grid[subtitle]" class="form-control"
                                   value="{{ $catGrid['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categories to Show</label>
                            <input type="number" name="categories_grid[limit]" class="form-control"
                                   value="{{ $catGrid['limit'] ?? 6 }}" min="2" max="12">
                        </div>
                        <input type="hidden" name="categories_grid[enabled]" value="{{ ($catGrid['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Stats Counter ── --}}
        <div class="cms-section" id="sec-stats-counter">
            @php $stats = $settings['stats_counter'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="ti tabler-chart-bar text-primary"></i> Stats Counter</h6>
                    <button type="button" class="btn btn-sm btn-label-primary" id="addStatBtn">
                        <i class="ti tabler-plus me-1"></i> Add Stat
                    </button>
                </div>
                <div class="card-body">
                    <div id="statsContainer">
                        @foreach(($stats['items'] ?? []) as $i => $item)
                        <div class="stat-item" data-idx="{{ $i }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Value</label>
                                    <input type="text" class="form-control stat-value" value="{{ $item['value'] ?? '' }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Label</label>
                                    <input type="text" class="form-control stat-label" value="{{ $item['label'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Icon</label>
                                    <input type="text" class="form-control stat-icon" value="{{ $item['icon'] ?? '' }}" placeholder="tabler-package">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-label-danger w-100 remove-stat-btn">
                                        <i class="ti tabler-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="stats_counter" id="statsCounterInput">
                    <input type="hidden" class="section-enabled-hidden" data-key="stats_counter" value="{{ ($stats['enabled'] ?? true) ? '1' : '0' }}">
                </div>
            </div>
        </div>

        {{-- ── Hot Deals ── --}}
        <div class="cms-section" id="sec-hot-deals">
            @php $deals = $settings['hot_deals'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-flame text-primary"></i> Hot Deals</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="hot_deals[title]" class="form-control"
                                   value="{{ $deals['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="hot_deals[subtitle]" class="form-control"
                                   value="{{ $deals['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Products to Show</label>
                            <input type="number" name="hot_deals[limit]" class="form-control"
                                   value="{{ $deals['limit'] ?? 5 }}" min="1" max="12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Show Timer</label>
                            <select name="hot_deals[show_timer]" class="form-select">
                                <option value="1" {{ ($deals['show_timer'] ?? true) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !($deals['show_timer'] ?? true) ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Banner Discount Text</label>
                            <input type="text" name="hot_deals[banner_discount]" class="form-control"
                                   value="{{ $deals['banner_discount'] ?? '80%' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner Event Text</label>
                            <input type="text" name="hot_deals[banner_event]" class="form-control"
                                   value="{{ $deals['banner_event'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banner Image</label>
                            <div class="img-upload-zone" onclick="document.getElementById('hotDealsBannerImg').click()">
                                @if(!empty($deals['banner_image']))
                                    <img src="{{ $deals['banner_image'] }}" alt="Banner">
                                @else
                                    <div><i class="ti tabler-cloud-upload fs-3 text-muted"></i><br><small class="text-muted">Click to upload</small></div>
                                @endif
                            </div>
                            <input type="file" id="hotDealsBannerImg" name="hot_deals_banner_image" accept="image/*" class="d-none">
                        </div>
                        <input type="hidden" name="hot_deals[enabled]" value="{{ ($deals['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Blog Section ── --}}
        <div class="cms-section" id="sec-blog-section">
            @php $blog = $settings['blog_section'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-article text-primary"></i> Blog Section</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" name="blog_section[title]" class="form-control"
                                   value="{{ $blog['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="blog_section[subtitle]" class="form-control"
                                   value="{{ $blog['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Posts to Show</label>
                            <input type="number" name="blog_section[limit]" class="form-control"
                                   value="{{ $blog['limit'] ?? 4 }}" min="1" max="12">
                        </div>
                        <input type="hidden" name="blog_section[enabled]" value="{{ ($blog['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Newsletter ── --}}
        <div class="cms-section" id="sec-newsletter">
            @php $news = $settings['newsletter'] ?? []; @endphp
            <div class="card section-card">
                <div class="card-header"><h6><i class="ti tabler-mail text-primary"></i> Newsletter</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="newsletter[title]" class="form-control"
                                   value="{{ $news['title'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Subtitle</label>
                            <input type="text" name="newsletter[subtitle]" class="form-control"
                                   value="{{ $news['subtitle'] ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="newsletter[description]" class="form-control" rows="2">{{ $news['description'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Button Text</label>
                            <input type="text" name="newsletter[button_text]" class="form-control"
                                   value="{{ $news['button_text'] ?? 'Subscribe' }}">
                        </div>
                        <input type="hidden" name="newsletter[enabled]" value="{{ ($news['enabled'] ?? true) ? '1' : '0' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
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
            const target = 'sec-' + this.dataset.section;
            document.getElementById(target)?.classList.add('active');
        });
    });

    // Section enabled toggles → sync hidden fields
    document.querySelectorAll('.section-enabled-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const key = this.dataset.key;
            const hidden = document.querySelector(`input[name="${key}[enabled]"]`);
            if (hidden) hidden.value = this.checked ? '1' : '0';
        });
    });

    // Simple drag reorder for sections
    const list = document.getElementById('sectionsOrderList');
    let dragEl = null;
    list.querySelectorAll('.order-item').forEach(item => {
        item.setAttribute('draggable', 'true');
        item.addEventListener('dragstart', function(e) {
            dragEl = this;
            this.style.opacity = '0.5';
        });
        item.addEventListener('dragend', function() {
            this.style.opacity = '';
            updateSectionsOrder();
        });
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            const rect = this.getBoundingClientRect();
            const mid = rect.top + rect.height / 2;
            if (e.clientY < mid) {
                list.insertBefore(dragEl, this);
            } else {
                list.insertBefore(dragEl, this.nextSibling);
            }
        });
    });

    function updateSectionsOrder() {
        const items = list.querySelectorAll('.order-item');
        const order = Array.from(items).map(i => i.dataset.section);
        document.getElementById('sectionsOrderInput').value = JSON.stringify(order);
    }

    // Stats counter management
    const statsContainer = document.getElementById('statsContainer');
    const addStatBtn = document.getElementById('addStatBtn');

    if (addStatBtn) {
        addStatBtn.addEventListener('click', function() {
            const idx = statsContainer.children.length;
            const html = `
                <div class="stat-item" data-idx="${idx}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3"><label class="form-label">Value</label><input type="text" class="form-control stat-value"></div>
                        <div class="col-md-4"><label class="form-label">Label</label><input type="text" class="form-control stat-label"></div>
                        <div class="col-md-3"><label class="form-label">Icon</label><input type="text" class="form-control stat-icon" placeholder="tabler-package"></div>
                        <div class="col-md-2"><button type="button" class="btn btn-sm btn-label-danger w-100 remove-stat-btn"><i class="ti tabler-trash"></i></button></div>
                    </div>
                </div>`;
            statsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    statsContainer?.addEventListener('click', function(e) {
        if (e.target.closest('.remove-stat-btn')) {
            e.target.closest('.stat-item').remove();
        }
    });

    // Before submit: collect stats
    document.getElementById('homepageForm').addEventListener('submit', function() {
        const items = [];
        statsContainer?.querySelectorAll('.stat-item').forEach(item => {
            items.push({
                value: item.querySelector('.stat-value').value,
                label: item.querySelector('.stat-label').value,
                icon: item.querySelector('.stat-icon').value,
            });
        });
        const enabled = document.querySelector('.section-enabled-hidden[data-key="stats_counter"]');
        document.getElementById('statsCounterInput').value = JSON.stringify({
            enabled: enabled ? enabled.value : '1',
            items: items,
        });
        updateSectionsOrder();
    });

    // Image preview
    ['promoBannerImg', 'hotDealsBannerImg'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('change', function() {
            if (this.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const zone = this.previousElementSibling || this.closest('.col-md-6').querySelector('.img-upload-zone');
                    if (zone) zone.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});
</script>
@endpush
