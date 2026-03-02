<div class="settings-nav">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3">
            <div class="settings-nav-group">
                <div class="settings-nav-header">Platform</div>
                <a href="{{ route('settings.general') }}" class="settings-nav-item {{ request()->routeIs('settings.general') ? 'active' : '' }}">
                    <i class="ti tabler-adjustments-horizontal"></i> General
                </a>
                <a href="{{ route('settings.branding') }}" class="settings-nav-item {{ request()->routeIs('settings.branding') ? 'active' : '' }}">
                    <i class="ti tabler-palette"></i> Branding
                </a>
                <a href="{{ route('settings.security') }}" class="settings-nav-item {{ request()->routeIs('settings.security') ? 'active' : '' }}">
                    <i class="ti tabler-shield-lock"></i> Security
                </a>
                <a href="{{ route('settings.registration') }}" class="settings-nav-item {{ request()->routeIs('settings.registration') ? 'active' : '' }}">
                    <i class="ti tabler-user-plus"></i> Registration
                </a>
            </div>

            <div class="settings-nav-group">
                <div class="settings-nav-header">Commerce</div>
                <a href="{{ route('settings.store') }}" class="settings-nav-item {{ request()->routeIs('settings.store') ? 'active' : '' }}">
                    <i class="ti tabler-building-store"></i> Store & Commerce
                </a>
                <a href="{{ route('settings.checkout') }}" class="settings-nav-item {{ request()->routeIs('settings.checkout') ? 'active' : '' }}">
                    <i class="ti tabler-shopping-cart"></i> Checkout
                </a>
                <a href="{{ route('settings.products') }}" class="settings-nav-item {{ request()->routeIs('settings.products') ? 'active' : '' }}">
                    <i class="ti tabler-package"></i> Products
                </a>
                <a href="{{ route('settings.vendor') }}" class="settings-nav-item {{ request()->routeIs('settings.vendor') ? 'active' : '' }}">
                    <i class="ti tabler-users-group"></i> Vendor / Seller
                </a>
                <a href="{{ route('settings.affiliate') }}" class="settings-nav-item {{ request()->routeIs('settings.affiliate') ? 'active' : '' }}">
                    <i class="ti tabler-affiliate"></i> Affiliate Program
                </a>
                <a href="{{ route('settings.refund-escrow') }}" class="settings-nav-item {{ request()->routeIs('settings.refund-escrow') ? 'active' : '' }}">
                    <i class="ti tabler-receipt-refund"></i> Refund & Escrow
                </a>
                <a href="{{ route('settings.reviews') }}" class="settings-nav-item {{ request()->routeIs('settings.reviews') ? 'active' : '' }}">
                    <i class="ti tabler-star"></i> Reviews & Ratings
                </a>
            </div>

            <div class="settings-nav-group">
                <div class="settings-nav-header">Financial</div>
                <a href="{{ route('settings.wallet') }}" class="settings-nav-item {{ request()->routeIs('settings.wallet') ? 'active' : '' }}">
                    <i class="ti tabler-wallet"></i> Payment & Wallet
                </a>
                <a href="{{ route('settings.invoice') }}" class="settings-nav-item {{ request()->routeIs('settings.invoice') ? 'active' : '' }}">
                    <i class="ti tabler-file-invoice"></i> Invoice
                </a>
                <a href="{{ route('settings.currency') }}" class="settings-nav-item {{ request()->routeIs('settings.currency') ? 'active' : '' }}">
                    <i class="ti tabler-currency-dollar"></i> Currency & Locale
                </a>
            </div>

            <div class="settings-nav-group">
                <div class="settings-nav-header">Communication</div>
                <a href="{{ route('settings.email') }}" class="settings-nav-item {{ request()->routeIs('settings.email') ? 'active' : '' }}">
                    <i class="ti tabler-mail-cog"></i> Email / SMTP
                </a>
                <a href="{{ route('settings.notifications') }}" class="settings-nav-item {{ request()->routeIs('settings.notifications') ? 'active' : '' }}">
                    <i class="ti tabler-bell"></i> Notifications
                </a>
            </div>

            <div class="settings-nav-group">
                <div class="settings-nav-header">Content & Marketing</div>
                <a href="{{ route('settings.seo') }}" class="settings-nav-item {{ request()->routeIs('settings.seo') ? 'active' : '' }}">
                    <i class="ti tabler-search"></i> SEO
                </a>
                <a href="{{ route('settings.social') }}" class="settings-nav-item {{ request()->routeIs('settings.social') ? 'active' : '' }}">
                    <i class="ti tabler-share"></i> Social Links
                </a>
                <a href="{{ route('settings.legal') }}" class="settings-nav-item {{ request()->routeIs('settings.legal') ? 'active' : '' }}">
                    <i class="ti tabler-gavel"></i> Legal Pages
                </a>
                <a href="{{ route('settings.website') }}" class="settings-nav-item {{ request()->routeIs('settings.website') ? 'active' : '' }}">
                    <i class="ti tabler-layout"></i> Website / CMS
                </a>
            </div>

            <div class="settings-nav-group">
                <div class="settings-nav-header">System</div>
                <a href="{{ route('settings.api-integrations') }}" class="settings-nav-item {{ request()->routeIs('settings.api-integrations') ? 'active' : '' }}">
                    <i class="ti tabler-api"></i> API & Integrations
                </a>
                <a href="{{ route('settings.maintenance') }}" class="settings-nav-item {{ request()->routeIs('settings.maintenance') ? 'active' : '' }}">
                    <i class="ti tabler-tool"></i> Maintenance
                </a>
                <a href="{{ route('settings.ai') }}" class="settings-nav-item {{ request()->routeIs('settings.ai') ? 'active' : '' }}">
                    <i class="ti tabler-robot"></i> AI Configuration
                </a>
            </div>
        </div>
    </div>
</div>
