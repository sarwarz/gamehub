<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                @if(!empty($appSettings['logo']))
                <img src="{{ asset($appSettings['logo']) }}" alt="{{ $appSettings['site_name'] ?? config('app.name') }}" style="height: 28px; width: auto;" class="app-brand-logo-light">
                @if(!empty($appSettings['logo_dark']))
                <img src="{{ asset($appSettings['logo_dark']) }}" alt="{{ $appSettings['site_name'] ?? config('app.name') }}" style="height: 28px; width: auto; display:none;" class="app-brand-logo-dark">
                @endif
                @else
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor"/>
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616"/>
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor"/>
                    </svg>
                </span>
                @endif
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">{{ $appSettings['site_name'] ?? config('app.name') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
            <i class="icon-base ti tabler-x d-block d-xl-none"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    @php if (!function_exists('menuItemActive')) { function menuItemActive(array $patterns, string $output = 'active'): string { foreach ($patterns as $pattern) { if (request()->routeIs($pattern)) { return $output; } } return ''; } }
    @endphp

    <ul class="menu-inner py-1">
    <!-- Dashboard -->
    @if(auth()->user()->hasPermission('dashboard') && Route::has('dashboard'))
    <li class="menu-item {{ menuItemActive(['dashboard']) }}">
        <a href="{{ route('dashboard') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-smart-home"></i>
            <div data-i18n="Dashboard">Dashboard</div>
        </a>
    </li>
    @endif

    @if(auth()->user()->hasPermission('reports') && Route::has('reports.index'))
    <li class="menu-item {{ menuItemActive(['reports.*']) }}">
        <a href="{{ route('reports.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-chart-bar"></i>
            <div data-i18n="Reports & Analytics">Reports & Analytics</div>
        </a>
    </li>
    @endif


     <!-- Products Attributes -->
    @if(
        auth()->user()->hasPermission('categories') ||
        auth()->user()->hasPermission('platforms') ||
        auth()->user()->hasPermission('types') ||
        auth()->user()->hasPermission('regions') ||
        auth()->user()->hasPermission('languages') ||
        auth()->user()->hasPermission('workson') ||
        auth()->user()->hasPermission('developers') ||
        auth()->user()->hasPermission('publishers')
    )
    <li class="menu-item {{ menuItemActive([
        'categories.*','platforms.*','types.*',
        'regions.*','languages.*','workson.*','developers.*','publishers.*', 'labels.*'
    ], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-tags"></i>
            <div data-i18n="Product Attributes">Product Attributes</div>
        </a>
        <ul class="menu-sub">
            @if(auth()->user()->hasPermission('categories') && Route::has('categories.index'))
            <li class="menu-item {{ menuItemActive(['categories.*']) }}">
                <a href="{{ route('categories.index') }}" class="menu-link">
                    <div data-i18n="Category">Category</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('platforms') && Route::has('platforms.index'))
            <li class="menu-item {{ menuItemActive(['platforms.*']) }}">
                <a href="{{ route('platforms.index') }}" class="menu-link">
                    <div data-i18n="Platform">Platform</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('types') && Route::has('types.index'))
            <li class="menu-item {{ menuItemActive(['types.*']) }}">
                <a href="{{ route('types.index') }}" class="menu-link">
                    <div data-i18n="Type">Type</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('regions') && Route::has('regions.index'))
            <li class="menu-item {{ menuItemActive(['regions.*']) }}">
                <a href="{{ route('regions.index') }}" class="menu-link">
                    <div data-i18n="Region">Region</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('languages') && Route::has('languages.index'))
            <li class="menu-item {{ menuItemActive(['languages.*']) }}">
                <a href="{{ route('languages.index') }}" class="menu-link">
                    <div data-i18n="Languages">Languages</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('workson') && Route::has('workson.index'))
            <li class="menu-item {{ menuItemActive(['workson.*']) }}">
                <a href="{{ route('workson.index') }}" class="menu-link">
                    <div data-i18n="Works On">Works On</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('developers') && Route::has('developers.index'))
            <li class="menu-item {{ menuItemActive(['developers.*']) }}">
                <a href="{{ route('developers.index') }}" class="menu-link">
                    <div data-i18n="Developers">Developers</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('publishers') && Route::has('publishers.index'))
            <li class="menu-item {{ menuItemActive(['publishers.*']) }}">
                <a href="{{ route('publishers.index') }}" class="menu-link">
                    <div data-i18n="Publishers">Publishers</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('labels') && Route::has('labels.index'))
            <li class="menu-item {{ menuItemActive(['labels.*']) }}">
                <a href="{{ route('labels.index') }}" class="menu-link">
                    <div data-i18n="Labels">Labels</div>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    <!-- Manage Products -->
    @if(auth()->user()->hasPermission('products'))
    <li class="menu-item {{ menuItemActive([
            'products.index',
            'products.inactive',
            'products.featured',
            'product-requests.index',
            'product-reviews.index'
        ], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-brand-databricks"></i>
            <div data-i18n="Manage Products">Manage Products</div>
        </a>

        <ul class="menu-sub">

            {{-- All Products --}}
            @if(Route::has('products.index'))
            <li class="menu-item {{ menuItemActive(['products.index']) }}">
                <a href="{{ route('products.index') }}" class="menu-link">
                    <div data-i18n="All Product">All Product</div>
                </a>
            </li>
            @endif

            {{-- Request Product --}}
            <li class="menu-item {{ menuItemActive(['product-requests.index']) }}">
                <a href="{{ route('product-requests.index') }}" class="menu-link">
                    <div data-i18n="Request Product">Request Product</div>
                </a>
            </li>

            {{-- Product Reviews --}}
            <li class="menu-item {{ menuItemActive(['product-reviews.index']) }}">
                <a href="{{ route('product-reviews.index') }}" class="menu-link">
                    <div data-i18n="Product Reviews">Product Reviews</div>
                </a>
            </li>

        </ul>
    </li>
    @endif


    @if(auth()->user()->hasPermission('seller-offers'))
    <li class="menu-item {{ menuItemActive(['seller-offers.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-pencil-dollar"></i>
            <div data-i18n="Manage Offers">Manage Offers</div>
        </a>

        <ul class="menu-sub">

            @if(Route::has('seller-offers.index'))
            <li class="menu-item {{ menuItemActive(['seller-offers.index']) }}">
                <a href="{{ route('seller-offers.index') }}" class="menu-link">
                    <div data-i18n="All Offers">All Offers</div>
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif




    <!-- Sellers -->
    @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
    <li class="menu-item {{ menuItemActive(['sellers.*', 'seller-withdraws.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-building-store"></i>
            <div data-i18n="Manage Sellers">Manage Sellers</div>
        </a>
        <ul class="menu-sub">

            @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
            <li class="menu-item {{ menuItemActive(['sellers.index']) }}">
                <a href="{{ route('sellers.index') }}" class="menu-link">All Sellers</a>
            </li>
            @endif

            <li class="menu-item {{ menuItemActive(['sellers.pending']) }}">
                <a href="{{ route('sellers.pending') }}" class="menu-link">Pending Sellers</a>
            </li>

            <li class="menu-item {{ menuItemActive(['sellers.suspended']) }}">
                <a href="{{ route('sellers.suspended') }}" class="menu-link">Suspended Sellers</a>
            </li>

            <li class="menu-item {{ menuItemActive(['seller-withdraws.index']) }}">
                <a href="{{ route('seller-withdraws.index') }}" class="menu-link">Seller Withdraw</a>
            </li>

            <li class="menu-item {{ menuItemActive(['seller-withdraws.pending']) }}">
                <a href="{{ route('seller-withdraws.pending') }}" class="menu-link">Pending Withdraw</a>
            </li>

        </ul>
    </li>
    @endif

    <!-- Ecommerce -->
    @if(
        auth()->user()->hasPermission('orders') ||
        auth()->user()->hasPermission('transactions') ||
        auth()->user()->hasPermission('invoices') ||
        auth()->user()->hasPermission('coupons') ||
        auth()->user()->hasPermission('taxes') ||
        auth()->user()->hasPermission('currencies') ||
        auth()->user()->hasPermission('payment-methods')
    )
    <li class="menu-item {{ menuItemActive([
            'orders.*',
            'transactions.*',
            'invoices.*',
            'coupons.*',
            'taxes.*',
            'currencies.*',
            'payment-methods.*',
        ], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-garden-cart"></i>
            <div data-i18n="Ecommerce">Ecommerce</div>
        </a>

        <ul class="menu-sub">

            {{-- Orders --}}
            @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.*']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="Orders">Orders</div>
                </a>
            </li>
            @endif

            {{-- Transactions --}}
            @if(auth()->user()->hasPermission('transactions') && Route::has('transactions.index'))
            <li class="menu-item {{ menuItemActive(['transactions.*']) }}">
                <a href="{{ route('transactions.index') }}" class="menu-link">
                    <div data-i18n="Transactions">Transactions</div>
                </a>
            </li>
            @endif

            {{-- Invoices --}}
            @if(auth()->user()->hasPermission('invoices') && Route::has('invoices.index'))
            <li class="menu-item {{ menuItemActive(['invoices.*']) }}">
                <a href="{{ route('invoices.index') }}" class="menu-link">
                    <div data-i18n="Invoices">Invoices</div>
                </a>
            </li>
            @endif

            {{-- Coupons --}}
            @if(auth()->user()->hasPermission('coupons') && Route::has('coupons.index'))
            <li class="menu-item {{ menuItemActive(['coupons.*']) }}">
                <a href="{{ route('coupons.index') }}" class="menu-link">
                    <div data-i18n="Coupons">Coupons</div>
                </a>
            </li>
            @endif

            {{-- Taxes --}}
            @if(auth()->user()->hasPermission('taxes') && Route::has('taxes.index'))
            <li class="menu-item {{ menuItemActive(['taxes.*']) }}">
                <a href="{{ route('taxes.index') }}" class="menu-link">
                    <div data-i18n="Taxes">Taxes</div>
                </a>
            </li>
            @endif

            {{-- Currencies --}}
            @if(auth()->user()->hasPermission('currencies') && Route::has('currencies.index'))
            <li class="menu-item {{ menuItemActive(['currencies.*']) }}">
                <a href="{{ route('currencies.index') }}" class="menu-link">
                    <div data-i18n="Currencies">Currencies</div>
                </a>
            </li>
            @endif

            {{-- Payment Methods --}}
            @if(auth()->user()->hasPermission('payment-methods') && Route::has('payment-methods.index'))
            <li class="menu-item {{ menuItemActive(['payment-methods.*']) }}">
                <a href="{{ route('payment-methods.index') }}" class="menu-link">
                    <div data-i18n="Payment Methods">Payment Methods</div>
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif



    <!-- Manage Website -->
    @if(auth()->user()->hasPermission('sliders'))
    <li class="menu-item {{ menuItemActive(['sliders.*', 'menus.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-world"></i>
            <div data-i18n="Manage Website">Manage Website</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item {{ menuItemActive(['sliders.*']) }}">
                <a href="{{ route('sliders.index') }}" class="menu-link">
                    <div data-i18n="Slider">Slider</div>
                </a>
            </li>

            <li class="menu-item {{ menuItemActive(['menus.*']) }}">
                <a href="{{ route('menus.index') }}" class="menu-link">
                    <div data-i18n="Menus">Menus</div>
                </a>
            </li>

        </ul>
    </li>
    @endif


    <!-- Pages -->
    @if(auth()->user()->hasPermission('pages'))
    <li class="menu-item {{ menuItemActive(['pages.*', 'faqs.*', 'static-pages.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-layout-columns"></i>
            <div data-i18n="Manage Pages">Manage Pages</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item {{ menuItemActive(['pages.*']) }}">
                <a href="{{ route('pages.index') }}" class="menu-link">
                    <div data-i18n="Custom Page">Custom Page</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('dashboard/static-pages/about') ? 'active' : '' }}">
                <a href="{{ route('static-pages.edit', 'about') }}" class="menu-link">
                    <div data-i18n="About Us">About Us</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('dashboard/static-pages/contact') ? 'active' : '' }}">
                <a href="{{ route('static-pages.edit', 'contact') }}" class="menu-link">
                    <div data-i18n="Contact Us">Contact Us</div>
                </a>
            </li>

            <li class="menu-item {{ menuItemActive(['faqs.*']) }}">
                <a href="{{ route('faqs.index') }}" class="menu-link">
                    <div data-i18n="FAQ">FAQ</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('dashboard/static-pages/privacy') ? 'active' : '' }}">
                <a href="{{ route('static-pages.edit', 'privacy') }}" class="menu-link">
                    <div data-i18n="Privacy Policy">Privacy Policy</div>
                </a>
            </li>

            <li class="menu-item {{ request()->is('dashboard/static-pages/terms') ? 'active' : '' }}">
                <a href="{{ route('static-pages.edit', 'terms') }}" class="menu-link">
                    <div data-i18n="Terms & Conditions">Terms & Conditions</div>
                </a>
            </li>

        </ul>
    </li>
    @endif

    <!-- Blogs -->
    @if(
        auth()->user()->hasPermission('blogs') ||
        auth()->user()->hasPermission('blog-categories') ||
        auth()->user()->hasPermission('blog-comments')
    )
    <li class="menu-item {{ menuItemActive(['blogs.*', 'blog-categories.*', 'blog-comments.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-notebook"></i>
            <div data-i18n="Manage Blogs">Manage Blogs</div>
        </a>

        <ul class="menu-sub">

            @if(auth()->user()->hasPermission('blog-categories'))
            <li class="menu-item {{ menuItemActive(['blog-categories.*']) }}">
                <a href="{{ route('blog-categories.index') }}" class="menu-link">
                    <div data-i18n="Categories">Categories</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('blogs'))
            <li class="menu-item {{ menuItemActive(['blogs.index']) }}">
                <a href="{{ route('blogs.index') }}" class="menu-link">
                    <div data-i18n="All Blogs">All Blogs</div>
                </a>
            </li>

            <li class="menu-item {{ menuItemActive(['blogs.popular']) }}">
                <a href="{{ route('blogs.popular') }}" class="menu-link">
                    <div data-i18n="Popular Blogs">Popular Blogs</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('blog-comments'))
            <li class="menu-item {{ menuItemActive(['blog-comments.*']) }}">
                <a href="{{ route('blog-comments.index') }}" class="menu-link">
                    <div data-i18n="Comments">Comments</div>
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif


    <!-- Wallet Management -->
    @if(auth()->user()->hasPermission('wallets'))
    <li class="menu-item {{ menuItemActive(['wallets.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-wallet"></i>
            <div data-i18n="Wallet Management">Wallet Management</div>
        </a>

        <ul class="menu-sub">

            <li class="menu-item {{ menuItemActive(['wallets.index']) }}">
                <a href="{{ route('wallets.index') }}" class="menu-link">
                    <div data-i18n="User Wallets">User Wallets</div>
                </a>
            </li>

            <li class="menu-item {{ menuItemActive(['wallets.all.transactions']) }}">
                <a href="{{ route('wallets.all.transactions') }}" class="menu-link">
                    <div data-i18n="Transactions">Transactions</div>
                </a>
            </li>


        </ul>
    </li>
    @endif



    <!-- Support Tickets -->
    @if(auth()->user()->hasPermission('support-tickets'))
    <li class="menu-item {{ menuItemActive(['support-tickets.*', 'canned-responses.*', 'ticket-departments.*'], 'open active') }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-lifebuoy"></i>
            <div data-i18n="Support Tickets">Support Tickets</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ menuItemActive(['support-tickets.index']) && !request('status') ? 'active' : '' }}">
                <a href="{{ route('support-tickets.index') }}" class="menu-link">
                    <div data-i18n="All Tickets">All Tickets</div>
                </a>
            </li>
            <li class="menu-item {{ request('status') === 'escalated' ? 'active' : '' }}">
                <a href="{{ route('support-tickets.index', ['status' => 'escalated']) }}" class="menu-link">
                    <div data-i18n="Escalated">Escalated</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['ticket-departments.*']) }}">
                <a href="{{ route('ticket-departments.index') }}" class="menu-link">
                    <div data-i18n="Departments">Departments</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['canned-responses.*']) }}">
                <a href="{{ route('canned-responses.index') }}" class="menu-link">
                    <div data-i18n="Canned Responses">Canned Responses</div>
                </a>
            </li>
        </ul>
    </li>
    @endif


    <!-- Affiliates -->
    @if(auth()->user()->hasPermission('affiliates'))
    <li class="menu-item {{ menuItemActive(['affiliates.*', 'affiliate-commissions.*', 'affiliate-withdrawals.*', 'affiliate-tiers.*'], 'open active') }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-affiliate"></i>
            <div data-i18n="Affiliates">Affiliates</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ menuItemActive(['affiliates.index']) }}">
                <a href="{{ route('affiliates.index') }}" class="menu-link">
                    <div data-i18n="All Affiliates">All Affiliates</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['affiliates.pending']) }}">
                <a href="{{ route('affiliates.pending') }}" class="menu-link">
                    <div data-i18n="Pending">Pending Applications</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['affiliate-commissions.*']) }}">
                <a href="{{ route('affiliate-commissions.index') }}" class="menu-link">
                    <div data-i18n="Commissions">Commissions</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['affiliate-withdrawals.*']) }}">
                <a href="{{ route('affiliate-withdrawals.index') }}" class="menu-link">
                    <div data-i18n="Withdrawals">Withdrawals</div>
                </a>
            </li>
            <li class="menu-item {{ menuItemActive(['affiliate-tiers.*']) }}">
                <a href="{{ route('affiliate-tiers.index') }}" class="menu-link">
                    <div data-i18n="Tiers">Tiers</div>
                </a>
            </li>
        </ul>
    </li>
    @endif

    <!-- Communications-->
    @if(
        auth()->user()->hasPermission('subscribers') ||
        auth()->user()->hasPermission('contact-messages')
    )
    <li class="menu-item {{ menuItemActive(['subscribers.*', 'contact-messages.*'], 'open active') }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-mail"></i>
            <div data-i18n="Communications">Communications</div>
        </a>
        <ul class="menu-sub">
            @if(auth()->user()->hasPermission('subscribers'))
            <li class="menu-item {{ menuItemActive(['subscribers.*']) }}">
                <a href="{{ route('subscribers.index') }}" class="menu-link">
                    <div data-i18n="Subscribers">Subscribers</div>
                </a>
            </li>
            @endif
            @if(auth()->user()->hasPermission('contact-messages'))
            <li class="menu-item {{ menuItemActive(['contact-messages.*']) }}">
                <a href="{{ route('contact-messages.index') }}" class="menu-link">
                    <div data-i18n="Contact Messages">Contact Messages</div>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif




    <!-- Users -->
   @if(auth()->user()->hasPermission('users') && Route::has('users.index'))
    <li class="menu-item {{ menuItemActive(['users.*', 'customer.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-user"></i>
            <div data-i18n="Manage Users">Manage Users</div>
        </a>
        <ul class="menu-sub">

            @if(auth()->user()->hasPermission('users') && Route::has('users.index'))
            <li class="menu-item {{ menuItemActive(['users.index']) }}">
                <a href="{{ route('users.index') }}" class="menu-link">
                    <div data-i18n="All Users">All Users</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('users') && Route::has('customer.index'))
            <li class="menu-item {{ menuItemActive(['customer.index']) }}">
                <a href="{{ route('customer.index') }}" class="menu-link">
                    <div data-i18n="All Customer">All Customer</div>
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif

    <!-- Roles & Permissions -->
    @if(auth()->user()->hasPermission('roles') || auth()->user()->hasPermission('permissions'))
    <li class="menu-item {{ menuItemActive(['roles.*','permissions.*'], 'open active') }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-user-plus"></i>
            <div data-i18n="Roles & Permissions">Roles & Permissions</div>
        </a>
        <ul class="menu-sub">
            @if(auth()->user()->hasPermission('roles') && Route::has('roles.index'))
            <li class="menu-item {{ menuItemActive(['roles.*']) }}">
                <a href="{{ route('roles.index') }}" class="menu-link">
                    <div data-i18n="Roles">Roles</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('permissions') && Route::has('permissions.index'))
            <li class="menu-item {{ menuItemActive(['permissions.*']) }}">
                <a href="{{ route('permissions.index') }}" class="menu-link">
                    <div data-i18n="Permission">Permissions</div>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

     <!-- Settings -->
    @if(auth()->user()->hasPermission('settings'))
    <li class="menu-item {{ request()->routeIs('settings.*') ? 'open active' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-settings"></i>
            <div data-i18n="Settings">Settings</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ menuItemActive(['settings.general']) }}">
                <a href="{{ route('settings.general') }}" class="menu-link">General</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.branding']) }}">
                <a href="{{ route('settings.branding') }}" class="menu-link">Branding</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.security']) }}">
                <a href="{{ route('settings.security') }}" class="menu-link">Security</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.registration']) }}">
                <a href="{{ route('settings.registration') }}" class="menu-link">Registration</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.store']) }}">
                <a href="{{ route('settings.store') }}" class="menu-link">Store & Commerce</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.checkout']) }}">
                <a href="{{ route('settings.checkout') }}" class="menu-link">Checkout</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.products']) }}">
                <a href="{{ route('settings.products') }}" class="menu-link">Products</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.vendor']) }}">
                <a href="{{ route('settings.vendor') }}" class="menu-link">Vendor / Seller</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.affiliate']) }}">
                <a href="{{ route('settings.affiliate') }}" class="menu-link">Affiliate Program</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.refund-escrow']) }}">
                <a href="{{ route('settings.refund-escrow') }}" class="menu-link">Refund & Escrow</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.reviews']) }}">
                <a href="{{ route('settings.reviews') }}" class="menu-link">Reviews & Ratings</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.wallet']) }}">
                <a href="{{ route('settings.wallet') }}" class="menu-link">Payment & Wallet</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.invoice']) }}">
                <a href="{{ route('settings.invoice') }}" class="menu-link">Invoice</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.currency']) }}">
                <a href="{{ route('settings.currency') }}" class="menu-link">Currency & Locale</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.email']) }}">
                <a href="{{ route('settings.email') }}" class="menu-link">Email / SMTP</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.notifications']) }}">
                <a href="{{ route('settings.notifications') }}" class="menu-link">Notifications</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.seo']) }}">
                <a href="{{ route('settings.seo') }}" class="menu-link">SEO</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.social']) }}">
                <a href="{{ route('settings.social') }}" class="menu-link">Social Links</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.legal']) }}">
                <a href="{{ route('settings.legal') }}" class="menu-link">Legal Pages</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.website']) }}">
                <a href="{{ route('settings.website') }}" class="menu-link">Website / CMS</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.api-integrations']) }}">
                <a href="{{ route('settings.api-integrations') }}" class="menu-link">API & Integrations</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.maintenance']) }}">
                <a href="{{ route('settings.maintenance') }}" class="menu-link">Maintenance</a>
            </li>
            <li class="menu-item {{ menuItemActive(['settings.ai']) }}">
                <a href="{{ route('settings.ai') }}" class="menu-link">AI Configuration</a>
            </li>
        </ul>
    </li>
    @endif


</ul>

</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
