<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
            <div class="text-body">
                &#169;
                <script>
                    document.write(new Date().getFullYear());
                </script>
                {{ $appSettings['site_name'] ?? config('app.name') }}. All rights reserved.
            </div>
            @if(!empty($appSettings['footer_text']))
            <div class="text-muted small">
                {{ $appSettings['footer_text'] }}
            </div>
            @endif
        </div>
    </div>
</footer>