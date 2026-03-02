@extends('layouts.app')
@section('title', 'Menus')

@push('page-css')
<style>
.menu-card { border: 1px solid #e7e7e8; border-radius: 10px; transition: box-shadow .2s; }
.menu-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
.menu-card .card-body { padding: 1.25rem; }
.location-badge { font-size: .75rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="ti tabler-menu-2 me-2"></i>Menus</h4>
        <p class="text-muted mb-0">Manage navigation menus for header, footer, and sidebar</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMenuModal">
        <i class="ti tabler-plus me-1"></i> New Menu
    </button>
</div>

<div class="row g-4">
    @forelse($menus as $menu)
    <div class="col-md-4">
        <div class="card menu-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">{{ $menu->name }}</h6>
                        <span class="badge bg-label-{{ match($menu->location) { 'header' => 'primary', 'footer' => 'info', default => 'warning' } }} location-badge">
                            {{ ucfirst($menu->location) }}
                        </span>
                        @if(!$menu->is_active)
                            <span class="badge bg-label-danger location-badge">Inactive</span>
                        @endif
                    </div>
                    <span class="badge bg-label-secondary">{{ $menu->all_items_count }} items</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-sm btn-label-primary flex-grow-1">
                        <i class="ti tabler-pencil me-1"></i> Edit Items
                    </a>
                    <button class="btn btn-sm btn-label-danger delete-menu-btn" data-id="{{ $menu->id }}">
                        <i class="ti tabler-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center py-5">
            <i class="ti tabler-menu-2 fs-1 text-muted mb-3 d-block"></i>
            <h6>No menus yet</h6>
            <p class="text-muted">Create your first menu to manage navigation.</p>
        </div></div>
    </div>
    @endforelse
</div>

{{-- Create Menu Modal --}}
<div class="modal fade" id="createMenuModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form action="{{ route('menus.store') }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Create Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Menu Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Main Navigation">
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <select name="location" class="form-select">
                        <option value="header">Header</option>
                        <option value="footer">Footer</option>
                        <option value="sidebar">Sidebar</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Menu</button>
            </div>
        </form>
    </div></div>
</div>
@endsection

@push('page-js')
<script>
document.querySelectorAll('.delete-menu-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({ title: 'Delete this menu?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Delete' }).then(r => {
            if (r.isConfirmed) {
                fetch(`{{ url('dashboard/menus') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                .then(r => r.json()).then(() => location.reload());
            }
        });
    });
});
</script>
@endpush
