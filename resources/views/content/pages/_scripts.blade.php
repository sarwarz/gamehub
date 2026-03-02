@push('page-js')
<script>
$(function () {

    // ─── Quill Editor ───
    var contentInput = document.getElementById('page-content-input');
    var quill = new Quill('#page-content-editor', {
        modules: { toolbar: '#page-content-toolbar' },
        placeholder: 'Write your page content here...',
        theme: 'snow'
    });

    // Sync Quill → hidden input on submit
    document.getElementById('page-form').addEventListener('submit', function () {
        var html = quill.root.innerHTML;
        contentInput.value = (html === '<p><br></p>') ? '' : html;
    });

    // ─── Auto Slug from Title ───
    var titleInput = document.getElementById('page-title');
    var slugInput  = document.getElementById('page-slug');
    var slugPreview = document.getElementById('slug-preview');
    var seoPreviewSlug = document.getElementById('seo-preview-slug');
    var isSlugManual = slugInput.value.length > 0;

    slugInput.addEventListener('input', function () {
        isSlugManual = this.value.length > 0;
        updateSlugPreview(this.value || slugify(titleInput.value));
    });

    titleInput.addEventListener('input', function () {
        if (!isSlugManual) {
            var slug = slugify(this.value);
            slugInput.setAttribute('placeholder', slug || 'custom-url-slug');
            updateSlugPreview(slug);
        }
        // Update SEO preview title if meta_title is empty
        var metaTitle = document.getElementById('meta-title');
        if (!metaTitle.value) {
            document.getElementById('seo-preview-title').textContent = this.value || 'Page Title';
        }
    });

    function slugify(str) {
        return str.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .substring(0, 255);
    }

    function updateSlugPreview(slug) {
        var baseUrl = '{{ url("/page/") }}/';
        slugPreview.innerHTML = slug ? 'URL: ' + baseUrl + '<strong>' + slug + '</strong>' : '';
        if (seoPreviewSlug) seoPreviewSlug.textContent = slug || 'page-slug';
    }

    // ─── Image Preview ───
    var imageInput = document.getElementById('featured-image-input');
    var imagePreview = document.getElementById('image-preview');
    var imagePlaceholder = document.getElementById('image-placeholder');

    imageInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('d-none');
                if (imagePlaceholder) imagePlaceholder.classList.add('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ─── SEO Character Counters ───
    var metaTitleInput = document.getElementById('meta-title');
    var metaDescInput  = document.getElementById('meta-description');
    var metaTitleCount = document.getElementById('meta-title-count');
    var metaDescCount  = document.getElementById('meta-desc-count');
    var seoPreviewTitle = document.getElementById('seo-preview-title');
    var seoPreviewDesc  = document.getElementById('seo-preview-desc');

    metaTitleInput.addEventListener('input', function () {
        metaTitleCount.textContent = this.value.length;
        metaTitleCount.className = this.value.length > 60 ? 'text-danger' : '';
        seoPreviewTitle.textContent = this.value || titleInput.value || 'Page Title';
    });

    metaDescInput.addEventListener('input', function () {
        metaDescCount.textContent = this.value.length;
        metaDescCount.className = this.value.length > 160 ? 'text-danger' : '';
        seoPreviewDesc.textContent = this.value || 'Page description will appear here...';
    });
});
</script>
@endpush
