@extends('layouts.app')
@section('title', 'FAQ Management')

@push('page-css')
<style>
.section-card { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 1.5rem; }
.section-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.5rem; }
.section-card .card-header h6 { margin: 0; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.section-card .card-body { padding: 1.25rem 1.5rem; }
.faq-category-block { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 16px; background: #fafafa; }
.faq-category-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #eee; cursor: pointer; }
.faq-category-header:hover { background: rgba(115,103,240,.04); }
.faq-category-body { padding: 12px 16px; }
.faq-item { display: flex; gap: 8px; padding: 10px; background: #fff; border: 1px solid #e7e7e8; border-radius: 8px; margin-bottom: 8px; }
.faq-item:hover { border-color: #c5c1f5; }
.faq-item textarea { resize: vertical; min-height: 38px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-help-circle me-2"></i>FAQ Management</h4>
        <p class="text-muted mb-0">Manage frequently asked questions and categories</p>
    </div>
</div>

<form action="{{ route('faqs.update') }}" method="POST" id="faqForm">
@csrf @method('PUT')

<div class="row">
    <div class="col-lg-4">
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-settings text-primary"></i> FAQ Page Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="title" class="form-control" value="{{ $settings['title'] ?? 'FAQ' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Hero Title</label>
                    <input type="text" name="hero_title" class="form-control" value="{{ $settings['hero_title'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Hero Subtitle</label>
                    <input type="text" name="hero_subtitle" class="form-control" value="{{ $settings['hero_subtitle'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ $settings['meta_title'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2">{{ $settings['meta_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="ti tabler-category text-primary"></i> Categories & Questions</h6>
                <button type="button" class="btn btn-sm btn-primary" id="addCategoryBtn">
                    <i class="ti tabler-plus me-1"></i> Add Category
                </button>
            </div>
            <div class="card-body">
                <div id="faqContainer">
                    {{-- Populated by JS --}}
                </div>
                <div id="emptyState" class="text-center py-4 text-muted" style="display:none;">
                    <i class="ti tabler-help-circle fs-1 d-block mb-2"></i>
                    <p>No FAQ categories yet. Add a category to get started.</p>
                </div>
            </div>
        </div>

        <input type="hidden" name="categories_data" id="categoriesDataInput">
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4"><i class="ti tabler-device-floppy me-1"></i> Save All Changes</button>
        </div>
    </div>
</div>
</form>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqContainer = document.getElementById('faqContainer');
    const emptyState = document.getElementById('emptyState');
    const initialData = @json($categories);
    let counter = 0;

    function createCategoryHtml(cat = {}) {
        const uid = 'cat_' + (counter++);
        return `
        <div class="faq-category-block" data-uid="${uid}" data-id="${cat.id || 'new'}">
            <div class="faq-category-header">
                <i class="ti tabler-grip-vertical text-muted" style="cursor:grab;"></i>
                <input type="text" class="form-control form-control-sm cat-name" placeholder="Category name" value="${cat.name || ''}" style="max-width:200px;">
                <input type="text" class="form-control form-control-sm cat-icon" placeholder="tabler-icon" value="${cat.icon || ''}" style="max-width:130px;">
                <div class="form-check form-switch mb-0 ms-auto">
                    <input class="form-check-input cat-active" type="checkbox" ${(cat.is_active !== false) ? 'checked' : ''}>
                </div>
                <button type="button" class="btn btn-sm btn-label-primary add-faq-btn"><i class="ti tabler-plus me-1"></i>Add Q&A</button>
                <button type="button" class="btn btn-icon btn-sm btn-label-danger remove-cat-btn"><i class="ti tabler-trash"></i></button>
            </div>
            <div class="faq-category-body faq-list"></div>
        </div>`;
    }

    function createFaqHtml(faq = {}) {
        const uid = 'faq_' + (counter++);
        return `
        <div class="faq-item" data-uid="${uid}" data-id="${faq.id || 'new'}">
            <div class="flex-grow-1">
                <input type="text" class="form-control form-control-sm faq-question mb-1" placeholder="Question" value="${(faq.question || '').replace(/"/g, '&quot;')}">
                <textarea class="form-control form-control-sm faq-answer" placeholder="Answer" rows="2">${faq.answer || ''}</textarea>
            </div>
            <div class="d-flex flex-column gap-1 align-items-center" style="min-width:36px;">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input faq-active" type="checkbox" ${(faq.is_active !== false) ? 'checked' : ''}>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-label-danger remove-faq-btn"><i class="ti tabler-x"></i></button>
            </div>
        </div>`;
    }

    function renderAll(data) {
        faqContainer.innerHTML = '';
        data.forEach(cat => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = createCategoryHtml(cat);
            const block = wrapper.firstElementChild;
            const faqList = block.querySelector('.faq-list');
            (cat.faqs || []).forEach(faq => {
                const fw = document.createElement('div');
                fw.innerHTML = createFaqHtml(faq);
                faqList.appendChild(fw.firstElementChild);
            });
            faqContainer.appendChild(block);
        });
        updateEmpty();
    }

    function updateEmpty() {
        emptyState.style.display = faqContainer.querySelector('.faq-category-block') ? 'none' : 'block';
    }

    function collectAll() {
        const cats = [];
        faqContainer.querySelectorAll('.faq-category-block').forEach(block => {
            const faqs = [];
            block.querySelectorAll('.faq-item').forEach(fi => {
                faqs.push({
                    id: fi.dataset.id || 'new',
                    question: fi.querySelector('.faq-question').value,
                    answer: fi.querySelector('.faq-answer').value,
                    is_active: fi.querySelector('.faq-active').checked,
                });
            });
            cats.push({
                id: block.dataset.id || 'new',
                name: block.querySelector('.cat-name').value,
                icon: block.querySelector('.cat-icon').value || null,
                is_active: block.querySelector('.cat-active').checked,
                faqs: faqs,
            });
        });
        return cats;
    }

    renderAll(initialData);

    document.getElementById('addCategoryBtn').addEventListener('click', function() {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = createCategoryHtml();
        faqContainer.appendChild(wrapper.firstElementChild);
        updateEmpty();
    });

    faqContainer.addEventListener('click', function(e) {
        if (e.target.closest('.add-faq-btn')) {
            const faqList = e.target.closest('.faq-category-block').querySelector('.faq-list');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = createFaqHtml();
            faqList.appendChild(wrapper.firstElementChild);
        }
        if (e.target.closest('.remove-cat-btn')) {
            e.target.closest('.faq-category-block').remove();
            updateEmpty();
        }
        if (e.target.closest('.remove-faq-btn')) {
            e.target.closest('.faq-item').remove();
        }
    });

    document.getElementById('faqForm').addEventListener('submit', function() {
        document.getElementById('categoriesDataInput').value = JSON.stringify(collectAll());
    });
});
</script>
@endpush
