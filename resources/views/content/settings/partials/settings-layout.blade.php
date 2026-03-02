@push('page-css')
<style>
.settings-nav {
    position: sticky;
    top: 80px;
}
.settings-nav-group {
    margin-bottom: 0.5rem;
}
.settings-nav-group:last-child {
    margin-bottom: 0;
}
.settings-nav-header {
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #a1acb8;
    padding: 0.625rem 0.875rem 0.25rem;
}
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    border-radius: 0.5rem;
    color: #566a7f;
    text-decoration: none;
    font-size: 0.8125rem;
    font-weight: 500;
    transition: all 0.15s ease;
}
.settings-nav-item:hover {
    background: rgba(115, 103, 240, 0.08);
    color: #7367f0;
}
.settings-nav-item.active {
    background: rgba(115, 103, 240, 0.12);
    color: #7367f0;
    font-weight: 600;
}
.settings-nav-item i {
    font-size: 1.125rem;
    width: 1.25rem;
    text-align: center;
}
.settings-header {
    background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);
    border-radius: 0.75rem;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    color: #fff;
}
.settings-header h4 {
    margin: 0 0 0.25rem;
    font-weight: 700;
    font-size: 1.25rem;
    color: #fff;
}
.settings-header p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.875rem;
}
.settings-header .settings-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.setting-card {
    border: 0;
    border-radius: 0.75rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 1.5rem;
    transition: box-shadow 0.2s;
}
.setting-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.setting-card .card-header {
    border-bottom: 1px solid #f0f0f3;
    padding: 1.25rem 1.5rem;
    background: transparent;
}
.setting-card .card-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.setting-card .card-header p {
    margin: 0.25rem 0 0;
    color: #a1acb8;
    font-size: 0.8125rem;
}
.setting-card .card-body {
    padding: 1.5rem;
}
.form-label-description {
    color: #a1acb8;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
.save-bar {
    position: sticky;
    bottom: 0;
    background: #fff;
    border: 1px solid #f0f0f3;
    border-radius: 0.75rem;
    padding: 1rem 1.5rem;
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    z-index: 10;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.04);
}
.setting-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 0;
    border-bottom: 1px solid #f5f5f9;
}
.setting-toggle:last-child {
    border-bottom: 0;
}
.setting-toggle-info h6 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
}
.setting-toggle-info p {
    margin: 0.125rem 0 0;
    font-size: 0.8125rem;
    color: #a1acb8;
}
.info-card {
    border: 0;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #f8f7fa 0%, #f0eefc 100%);
}
.info-card .card-body {
    padding: 1.25rem;
}
.info-card h6 {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}
.info-card ul {
    padding-left: 1.125rem;
    margin: 0;
}
.info-card ul li {
    font-size: 0.8125rem;
    color: #566a7f;
    margin-bottom: 0.375rem;
}
</style>
@endpush

@push('page-js')
<script>
function saveSettings(formId, route, options = {}) {
    const form = typeof formId === 'string' ? document.getElementById(formId) : formId;
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formEl = this;
        var btn = formEl.querySelector('button[type="submit"]');
        var btnHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        }

        fetch(route, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: new FormData(formEl)
        })
        .then(async r => {
            const data = await r.json();
            if (!r.ok) {
                const errors = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message || 'Validation failed.');
                throw new Error(errors);
            }
            return data;
        })
        .then(data => {
            Swal.fire({ icon: 'success', title: 'Saved!', text: data.message, timer: 1500, showConfirmButton: false })
                .then(function() { location.reload(); });
        })
        .catch(err => {
            Swal.fire({icon: 'error', title: 'Error', text: err.message || 'Failed to save settings.'});
            if (btn) { btn.disabled = false; btn.innerHTML = btnHtml; }
        });
    });
}
</script>
@endpush
