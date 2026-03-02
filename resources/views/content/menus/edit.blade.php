@extends('layouts.app')
@section('title', 'Edit Menu: ' . $menu->name)

@push('page-css')
<style>
.menu-item-row {
    display: flex; align-items: center; gap: 6px; padding: 8px 12px;
    background: #fff; border: 1px solid #e7e7e8; border-radius: 8px; margin-bottom: 6px;
    flex-wrap: wrap; transition: border-color .15s;
}
[data-bs-theme="dark"] .menu-item-row { background: #2f3349; border-color: #434968; }
.menu-item-row:hover { border-color: #7367f0; }
.menu-item-row .drag-handle { cursor: grab; color: #a1acb8; font-size: 1.1rem; }
.menu-children { margin-left: 28px; border-left: 2px solid #e7e7e8; padding-left: 12px; }
[data-bs-theme="dark"] .menu-children { border-color: #434968; }
.section-card { border: 1px solid #e7e7e8; border-radius: 10px; margin-bottom: 1.25rem; }
[data-bs-theme="dark"] .section-card { border-color: #434968; }
.section-card .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: .85rem 1.25rem; }
[data-bs-theme="dark"] .section-card .card-header { border-color: #434968; }
.section-card .card-header h6 { margin: 0; font-weight: 600; display: flex; align-items: center; gap: 8px; font-size: .875rem; }
.section-card .card-body { padding: 1rem 1.25rem; }
.type-badge-mega { background: #7367f0; color: #fff; font-size: .6rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: .3px; }
.type-badge-heading { background: #ff9f43; color: #fff; font-size: .6rem; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: .3px; }
.item-extra { display: flex; gap: 6px; width: 100%; margin-top: 6px; flex-wrap: wrap; align-items: center; }

/* Source picker panel */
.source-panel { max-height: 280px; overflow-y: auto; }
.source-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border: 1px solid #e7e7e8; border-radius: 6px; margin-bottom: 4px; cursor: pointer; transition: all .15s; font-size: .8125rem; }
[data-bs-theme="dark"] .source-item { border-color: #434968; }
.source-item:hover { border-color: #7367f0; background: rgba(115,103,240,.06); }
.source-item.selected { border-color: #7367f0; background: rgba(115,103,240,.1); }
.source-item .si-check { width: 16px; height: 16px; border: 2px solid #d4d4d4; border-radius: 4px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.source-item.selected .si-check { border-color: #7367f0; background: #7367f0; color: #fff; }
.source-tab { padding: 6px 12px; border: 1px solid #e7e7e8; border-radius: 6px; font-size: .75rem; cursor: pointer; transition: all .15s; white-space: nowrap; }
[data-bs-theme="dark"] .source-tab { border-color: #434968; }
.source-tab:hover, .source-tab.active { border-color: #7367f0; background: #7367f0; color: #fff; }
.source-tab .badge { font-size: .6rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-menu-2 me-2"></i>Edit Menu: {{ $menu->name }}</h4>
        <p class="text-muted mb-0">Build your navigation with mega menu & dynamic items</p>
    </div>
    <a href="{{ route('menus.index') }}" class="btn btn-label-secondary"><i class="ti tabler-arrow-left me-1"></i> Back</a>
</div>

<form action="{{ route('menus.update', $menu->id) }}" method="POST" id="menuForm">
@csrf @method('PUT')

<div class="row">
    {{-- LEFT COLUMN --}}
    <div class="col-lg-4">
        {{-- Menu Settings --}}
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-settings text-primary"></i> Menu Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $menu->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <select name="location" class="form-select form-select-sm">
                        <option value="header" {{ $menu->location === 'header' ? 'selected' : '' }}>Header</option>
                        <option value="footer" {{ $menu->location === 'footer' ? 'selected' : '' }}>Footer</option>
                        <option value="sidebar" {{ $menu->location === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $menu->is_active ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
        </div>

        {{-- Add Custom Link --}}
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-link text-info"></i> Custom Link</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <input type="text" id="customTitle" class="form-control form-control-sm" placeholder="Title">
                </div>
                <div class="mb-2">
                    <input type="text" id="customUrl" class="form-control form-control-sm" placeholder="/url or https://...">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary flex-grow-1" id="addCustomLink">
                        <i class="ti tabler-plus me-1"></i> Add Link
                    </button>
                    <button type="button" class="btn btn-sm btn-label-info" id="addMegaMenuBtn">
                        <i class="ti tabler-layout-grid me-1"></i> Mega Menu
                    </button>
                    <button type="button" class="btn btn-sm btn-label-warning" id="addHeadingBtn">
                        <i class="ti tabler-heading me-1"></i> Heading
                    </button>
                </div>
            </div>
        </div>

        {{-- Dynamic Sources --}}
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="ti tabler-database text-success"></i> Add from Data</h6>
            </div>
            <div class="card-body p-0">
                <div class="d-flex flex-wrap gap-1 p-3 pb-2 border-bottom" id="sourceTabs">
                    <span class="source-tab active" data-source="categories"><i class="ti tabler-category me-1"></i>Categories</span>
                    <span class="source-tab" data-source="platforms"><i class="ti tabler-device-gamepad me-1"></i>Platforms</span>
                    <span class="source-tab" data-source="types"><i class="ti tabler-tags me-1"></i>Types</span>
                    <span class="source-tab" data-source="regions"><i class="ti tabler-world me-1"></i>Regions</span>
                    <span class="source-tab" data-source="languages"><i class="ti tabler-language me-1"></i>Languages</span>
                    <span class="source-tab" data-source="works-on"><i class="ti tabler-device-desktop me-1"></i>Works On</span>
                    <span class="source-tab" data-source="developers"><i class="ti tabler-code me-1"></i>Developers</span>
                    <span class="source-tab" data-source="publishers"><i class="ti tabler-building me-1"></i>Publishers</span>
                    <span class="source-tab" data-source="products"><i class="ti tabler-box me-1"></i>Products</span>
                    <span class="source-tab" data-source="pages"><i class="ti tabler-file-text me-1"></i>Pages</span>
                    <span class="source-tab" data-source="blogs"><i class="ti tabler-news me-1"></i>Blogs</span>
                    <span class="source-tab" data-source="blog-categories"><i class="ti tabler-bookmark me-1"></i>Blog Cat.</span>
                    <span class="source-tab" data-source="faq-categories"><i class="ti tabler-help me-1"></i>FAQ Cat.</span>
                </div>
                <div class="p-3 pt-2">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text"><i class="ti tabler-search"></i></span>
                        <input type="text" class="form-control" id="sourceSearch" placeholder="Search items...">
                    </div>
                    <div class="source-panel" id="sourcePanel">
                        <div class="text-center text-muted py-3">
                            <i class="ti tabler-loader d-block fs-4 mb-1"></i>
                            <small>Loading...</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-label-secondary flex-grow-1" id="selectAllSource">Select All</button>
                        <button type="button" class="btn btn-sm btn-success flex-grow-1" id="addSelectedItems">
                            <i class="ti tabler-plus me-1"></i> Add Selected <span id="selectedCount" class="badge bg-white text-success ms-1">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Guide --}}
        <div class="card section-card">
            <div class="card-header"><h6><i class="ti tabler-info-circle text-primary"></i> Mega Menu Guide</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="type-badge-mega">megamenu</span>
                    <small class="text-muted ms-1">Top-level with multi-column panel</small>
                </div>
                <div class="mb-2">
                    <span class="type-badge-heading">heading</span>
                    <small class="text-muted ms-1">Column title (non-clickable)</small>
                </div>
                <div class="mb-3">
                    <span class="badge bg-label-secondary">link</span>
                    <small class="text-muted ms-1">Regular clickable link</small>
                </div>
                <p class="text-muted mb-0" style="font-size:.78rem;">
                    <strong>Structure:</strong> megamenu &rarr; category (left panel) &rarr; heading (column) &rarr; links
                </p>
            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-8">
        <div class="card section-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="ti tabler-list-tree text-primary"></i> Menu Structure</h6>
                <small class="text-muted" id="totalItemsLabel">0 items</small>
            </div>
            <div class="card-body">
                <div id="menuItemsContainer"></div>
                <div id="emptyState" class="text-center py-5 text-muted" style="display:none;">
                    <i class="ti tabler-list fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-1">No items yet</p>
                    <small>Use the left panel to add links, data items, or mega menus.</small>
                </div>
            </div>
        </div>

        <input type="hidden" name="items" id="itemsInput">
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-5"><i class="ti tabler-device-floppy me-1"></i> Save Menu</button>
        </div>
    </div>
</div>
</form>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container    = document.getElementById('menuItemsContainer');
    const emptyState   = document.getElementById('emptyState');
    const sourcePanel  = document.getElementById('sourcePanel');
    const sourceSearch = document.getElementById('sourceSearch');
    const linkableUrl  = "{{ route('menus.linkable-items') }}";
    let counter        = 0;
    let currentSource  = 'categories';
    let sourceCache    = {};
    const initialItems = @json($items);

    // ========== ITEM RENDERING ==========
    function typeLabel(type) {
        if (type === 'megamenu') return '<span class="type-badge-mega">mega</span>';
        if (type === 'heading') return '<span class="type-badge-heading">heading</span>';
        return '';
    }

    function esc(str) { return (str || '').replace(/"/g, '&quot;'); }

    function createItemHtml(data = {}, level = 0) {
        const uid = 'mi_' + (counter++);
        const type = data.type || 'link';
        const columns = data.columns || 4;
        const isActive = data.is_active !== false;
        const showUrl = type !== 'heading';
        const showCols = type === 'megamenu';

        return `
        <div class="menu-item-block" data-uid="${uid}" data-id="${data.id || 'new'}" data-level="${level}">
            <div class="menu-item-row">
                <i class="ti tabler-grip-vertical drag-handle"></i>
                ${typeLabel(type)}
                <select class="form-select form-select-sm item-type" style="max-width:100px;font-size:.75rem;">
                    <option value="link" ${type==='link'?'selected':''}>Link</option>
                    <option value="megamenu" ${type==='megamenu'?'selected':''}>Mega Menu</option>
                    <option value="heading" ${type==='heading'?'selected':''}>Heading</option>
                </select>
                <input type="text" class="form-control form-control-sm item-title" placeholder="Title" value="${esc(data.title)}" style="max-width:140px;">
                <input type="text" class="form-control form-control-sm item-url" placeholder="/url" value="${esc(data.url || '#')}" style="max-width:140px;${showUrl?'':'display:none;'}">
                <input type="text" class="form-control form-control-sm item-icon" placeholder="icon class" value="${esc(data.icon)}" style="max-width:90px;">
                <div class="form-check form-switch mb-0 ms-auto">
                    <input class="form-check-input item-active" type="checkbox" ${isActive?'checked':''}>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-label-primary add-child-btn" title="Add child"><i class="ti tabler-plus" style="font-size:.75rem;"></i></button>
                <button type="button" class="btn btn-icon btn-sm btn-label-danger remove-item-btn" title="Remove"><i class="ti tabler-x" style="font-size:.75rem;"></i></button>
                <div class="item-extra">
                    <div style="max-width:75px;${showCols?'':'display:none;'}" class="cols-wrap">
                        <input type="number" class="form-control form-control-sm item-columns" placeholder="Cols" value="${columns}" min="1" max="6" title="Number of columns">
                    </div>
                    <input type="text" class="form-control form-control-sm item-badge-text" placeholder="Badge" value="${esc(data.badge_text)}" style="max-width:75px;">
                    <input type="text" class="form-control form-control-sm item-badge-color" placeholder="#color" value="${esc(data.badge_color)}" style="max-width:75px;">
                    <select class="form-select form-select-sm item-target" style="max-width:80px;font-size:.75rem;">
                        <option value="_self" ${(data.target||'_self')==='_self'?'selected':''}>_self</option>
                        <option value="_blank" ${data.target==='_blank'?'selected':''}>_blank</option>
                    </select>
                </div>
            </div>
            <div class="menu-children"></div>
        </div>`;
    }

    function renderItems(items, parentEl, level = 0) {
        items.forEach(item => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = createItemHtml(item, level);
            const block = wrapper.firstElementChild;
            parentEl.appendChild(block);
            if (item.children && item.children.length) {
                renderItems(item.children, block.querySelector('.menu-children'), level + 1);
            }
        });
    }

    function updateState() {
        const total = container.querySelectorAll('.menu-item-block').length;
        emptyState.style.display = total ? 'none' : 'block';
        document.getElementById('totalItemsLabel').textContent = total + ' item' + (total !== 1 ? 's' : '');
    }

    function collectItems(parentEl) {
        const items = [];
        parentEl.querySelectorAll(':scope > .menu-item-block').forEach(block => {
            items.push({
                id: block.dataset.id || 'new',
                title: block.querySelector('.item-title').value,
                type: block.querySelector('.item-type').value,
                columns: parseInt(block.querySelector('.item-columns').value) || 4,
                url: block.querySelector('.item-url').value || '#',
                icon: block.querySelector('.item-icon').value || null,
                badge_text: block.querySelector('.item-badge-text').value || null,
                badge_color: block.querySelector('.item-badge-color').value || null,
                target: block.querySelector('.item-target').value,
                is_active: block.querySelector('.item-active').checked,
                children: collectItems(block.querySelector('.menu-children')),
            });
        });
        return items;
    }

    function addItem(data, target) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = createItemHtml(data);
        (target || container).appendChild(wrapper.firstElementChild);
        updateState();
    }

    renderItems(initialItems, container);
    updateState();

    // ========== CUSTOM LINKS / MEGA / HEADING ==========
    document.getElementById('addCustomLink').addEventListener('click', function() {
        const title = document.getElementById('customTitle').value.trim();
        const url = document.getElementById('customUrl').value.trim();
        if (!title) { Swal.fire('Error', 'Please enter a title.', 'warning'); return; }
        addItem({ type: 'link', title, url: url || '#' });
        document.getElementById('customTitle').value = '';
        document.getElementById('customUrl').value = '';
    });

    document.getElementById('addMegaMenuBtn').addEventListener('click', function() {
        const title = document.getElementById('customTitle').value.trim() || 'Mega Menu';
        addItem({ type: 'megamenu', title, url: '#', columns: 4 });
        document.getElementById('customTitle').value = '';
        document.getElementById('customUrl').value = '';
    });

    document.getElementById('addHeadingBtn').addEventListener('click', function() {
        const title = document.getElementById('customTitle').value.trim() || 'Heading';
        addItem({ type: 'heading', title, url: '#' });
        document.getElementById('customTitle').value = '';
        document.getElementById('customUrl').value = '';
    });

    // ========== MENU ITEM ACTIONS ==========
    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const block = e.target.closest('.menu-item-block');
            Swal.fire({
                title: 'Remove this item?',
                text: 'Children will also be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Remove'
            }).then(r => { if (r.isConfirmed) { block.remove(); updateState(); } });
        }
        if (e.target.closest('.add-child-btn')) {
            const block = e.target.closest('.menu-item-block');
            const parentType = block.querySelector('.item-type').value;
            const level = parseInt(block.dataset.level || 0) + 1;
            const childType = parentType === 'megamenu' ? 'heading' : 'link';
            const wrapper = document.createElement('div');
            wrapper.innerHTML = createItemHtml({ type: childType }, level);
            block.querySelector('.menu-children').appendChild(wrapper.firstElementChild);
            updateState();
        }
    });

    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('item-type')) {
            const row = e.target.closest('.menu-item-row');
            const type = e.target.value;
            row.querySelector('.item-url').style.display = type === 'heading' ? 'none' : '';
            row.querySelector('.cols-wrap').style.display = type === 'megamenu' ? '' : 'none';
        }
    });

    document.getElementById('menuForm').addEventListener('submit', function() {
        document.getElementById('itemsInput').value = JSON.stringify(collectItems(container));
    });

    // ========== DYNAMIC SOURCE PICKER ==========
    function loadSourceItems(source, search) {
        const cacheKey = source + '|' + (search || '');
        if (sourceCache[cacheKey]) {
            renderSourceItems(sourceCache[cacheKey]);
            return;
        }
        sourcePanel.innerHTML = '<div class="text-center text-muted py-3"><i class="ti tabler-loader d-block fs-4 mb-1 spinner-border spinner-border-sm"></i><small>Loading...</small></div>';
        fetch(linkableUrl + '?source=' + encodeURIComponent(source) + '&q=' + encodeURIComponent(search || ''))
            .then(r => r.json())
            .then(data => {
                if (data.success && data.items) {
                    sourceCache[cacheKey] = data.items;
                    renderSourceItems(data.items);
                }
            })
            .catch(() => {
                sourcePanel.innerHTML = '<div class="text-center text-danger py-3"><small>Failed to load</small></div>';
            });
    }

    function renderSourceItems(items) {
        if (!items.length) {
            sourcePanel.innerHTML = '<div class="text-center text-muted py-3"><i class="ti tabler-search-off d-block fs-4 mb-1"></i><small>No items found</small></div>';
            updateSelectedCount();
            return;
        }
        sourcePanel.innerHTML = items.map(item =>
            `<div class="source-item" data-title="${esc(item.title)}" data-url="${esc(item.url)}" data-slug="${esc(item.slug)}">
                <span class="si-check"><i class="ti tabler-check" style="font-size:.65rem;display:none;"></i></span>
                <span class="text-truncate flex-grow-1">${item.title}</span>
                <small class="text-muted text-truncate" style="max-width:120px;">${item.url}</small>
            </div>`
        ).join('');
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const count = sourcePanel.querySelectorAll('.source-item.selected').length;
        document.getElementById('selectedCount').textContent = count;
    }

    sourcePanel.addEventListener('click', function(e) {
        const item = e.target.closest('.source-item');
        if (!item) return;
        item.classList.toggle('selected');
        const icon = item.querySelector('.ti.tabler-check');
        icon.style.display = item.classList.contains('selected') ? '' : 'none';
        updateSelectedCount();
    });

    document.getElementById('selectAllSource').addEventListener('click', function() {
        const items = sourcePanel.querySelectorAll('.source-item');
        const allSelected = Array.from(items).every(i => i.classList.contains('selected'));
        items.forEach(item => {
            if (allSelected) {
                item.classList.remove('selected');
                item.querySelector('.ti.tabler-check').style.display = 'none';
            } else {
                item.classList.add('selected');
                item.querySelector('.ti.tabler-check').style.display = '';
            }
        });
        updateSelectedCount();
    });

    document.getElementById('addSelectedItems').addEventListener('click', function() {
        const selected = sourcePanel.querySelectorAll('.source-item.selected');
        if (!selected.length) { Swal.fire('Info', 'No items selected.', 'info'); return; }
        selected.forEach(si => {
            addItem({ type: 'link', title: si.dataset.title, url: si.dataset.url });
            si.classList.remove('selected');
            si.querySelector('.ti.tabler-check').style.display = 'none';
        });
        updateSelectedCount();
        Swal.fire({ icon: 'success', title: selected.length + ' items added', timer: 1200, showConfirmButton: false });
    });

    // Source tabs
    document.getElementById('sourceTabs').addEventListener('click', function(e) {
        const tab = e.target.closest('.source-tab');
        if (!tab) return;
        document.querySelectorAll('.source-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentSource = tab.dataset.source;
        sourceSearch.value = '';
        loadSourceItems(currentSource, '');
    });

    // Search debounce
    let searchTimer;
    sourceSearch.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadSourceItems(currentSource, sourceSearch.value.trim()), 300);
    });

    // Initial load
    loadSourceItems(currentSource, '');
});
</script>
@endpush
