<aside id="layout-menu" class="layout-menu menu-vertical menu">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                            fill="currentColor"
                        />
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616" />
                        <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                    </svg>
                </span>
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('app.name') }}</span>
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

    <!-- eCommerce Header -->
    <li class="menu-header small">
        <span class="menu-header-text" data-i18n="eCommerce">eCommerce</span>
    </li>

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
        'regions.*','languages.*','workson.*','developers.*','publishers.*'
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
        </ul>
    </li>
    @endif

    <!-- Manage Products -->
    @if( auth()->user()->hasPermission('products') )
    <li class="menu-item {{ menuItemActive(['products.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-brand-databricks"></i>
            <div data-i18n="Manage Products">Manage Products</div>
        </a>
        <ul class="menu-sub">
            
            @if(auth()->user()->hasPermission('products') && Route::has('products.index'))
            <li class="menu-item {{ menuItemActive(['products.index']) }}">
                <a href="{{ route('products.index') }}" class="menu-link">
                    <div data-i18n="All Product">All Product</div>
                </a>
            </li>
            @endif
        
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Inactive Product">Inactive Product</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Featured Product">Featured Product</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Request Product">Request Product</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Product Reviews">Product Reviews</div>
                </a>
            </li>

        </ul>
    </li>
    @endif

     <!-- Manage Offers -->
    @if( auth()->user()->hasPermission('seller-offers') )
    <li class="menu-item {{ menuItemActive(['seller-offers.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-pencil-dollar"></i>
            <div data-i18n="Manage Offers">Manage Offers</div>
        </a>
        <ul class="menu-sub">
            
            @if(auth()->user()->hasPermission('seller-offers') && Route::has('seller-offers.index'))
            <li class="menu-item {{ menuItemActive(['seller-offers.index']) }}">
                <a href="{{ route('seller-offers.index') }}" class="menu-link">
                    <div data-i18n="All Offers">All Offers</div>
                </a>
            </li>
            @endif

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Pending Offer">Pending Offer</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Rejected Offer">Rejected Offer</div>
                </a>
            </li>

        </ul>
    </li>
    @endif

    <!-- Orders -->

    @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
    <li class="menu-item {{ menuItemActive([
        'orders.*'
    ], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-sort-ascending-shapes"></i>
            <div data-i18n="Manage Orders">Manage Orders</div>
        </a>
        <ul class="menu-sub">
            
            @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.index']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="All Orders">All Orders</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.index']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="Pending Orders">Pending Orders</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.index']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="Processing Orders">Processing Orders</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.index']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="Completed Orders">Completed Orders</div>
                </a>
            </li>
            @endif

             @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
            <li class="menu-item {{ menuItemActive(['orders.index']) }}">
                <a href="{{ route('orders.index') }}" class="menu-link">
                    <div data-i18n="Declined Orders">Declined Orders</div>
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif


    <!-- Sellers -->
    @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
    <li class="menu-item {{ menuItemActive(['sellers.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-building-store"></i>
            <div data-i18n="Manage Sellers">Manage Sellers</div>
        </a>
        <ul class="menu-sub">
            
            @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
            <li class="menu-item {{ menuItemActive(['sellers.index']) }}">
                <a href="{{ route('sellers.index') }}" class="menu-link">
                    <div data-i18n="All Sellers">All Sellers</div>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
            <li class="menu-item {{ menuItemActive(['sellers.index']) }}">
                <a href="{{ route('sellers.index') }}" class="menu-link">
                    <div data-i18n="Pending Seller">Pending Seller </div>
                </a>
            </li>
            @endif

            

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Seller Withdraw">Seller Withdraw</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Pending  Withdraw">Pending  Withdraw</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Withdraw Method">Withdraw Method</div>
                </a>
            </li>

        </ul>
    </li>
    @endif

    <!-- Ecommerce -->
    <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-garden-cart"></i>
            <div data-i18n="Ecommerce">Ecommerce</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Campaign">Campaign</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Coupons">Coupons</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Tax">Tax</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Payment Method">Payment Method</div>
                </a>
            </li>


        </ul>
    </li>


    <!-- Manage Website -->
    @if(auth()->user()->hasPermission('currencies') && Route::has('currencies.index'))
    <li class="menu-item {{ menuItemActive(['currencies.*'], 'open active') }}">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-world"></i>
            <div data-i18n="Manage Website">Manage Website</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Slider">Slider</div>
                </a>
            </li>

            @if(auth()->user()->hasPermission('currencies') && Route::has('currencies.index'))
            <li class="menu-item {{ menuItemActive(['currencies.index']) }}">
                <a href="{{ route('currencies.index') }}" class="menu-link">
                    <div data-i18n="Currencies">Currencies</div>
                </a>
            </li>
            @endif

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Home Page">Home Page</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Shop Page">Shop Page</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Footer">Footer</div>
                </a>
            </li>


        </ul>
    </li>
    @endif


    <!-- Pages -->
    <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-layout-columns"></i>
            <div data-i18n="Manage Pages">Manage Pages</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Custom Page">Custom Page</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="About Us">About Us</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Contact Us">Contact Us</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="FAQ">FAQ</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Privacy Policy">Privacy Policy</div>
                </a>
            </li>
            

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Terms & Conditions">Terms & Conditions</div>
                </a>
            </li>


        </ul>
    </li>

    <!-- Blogs -->
    <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-pin"></i>
            <div data-i18n="Manage Blogs">Manage Blogs</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Categories">Categories</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Blogs">Blogs</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Comments">Comments</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Popular Blogs">Popular Blogs</div>
                </a>
            </li>


        </ul>
    </li>

    <!-- Wallet Management -->
    <li class="menu-item">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-wallet"></i>
            <div data-i18n="Manage Wallet">Manage Wallet</div>
        </a>

        <ul class="menu-sub">
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="User Wallets">User Wallets</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Transactions">Transactions</div>
                </a>
            </li>

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Wallet Settings">Wallet Settings</div>
                </a>
            </li>
        </ul>
    </li>


    <!-- Support Tickets -->

     <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-lifebuoy"></i>
            <div data-i18n="Support Tickets">Support Tickets</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="All Ticket">All Ticket</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Seller Ticket">Seller Ticket</div>
                </a>
            </li>


        </ul>
    </li>


    <!-- Communications-->
    <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-mail"></i>
            <div data-i18n="Communications">Communications</div>
        </a>
        <ul class="menu-sub">

            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Subscribers">Subscribers</div>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <div data-i18n="Contact Messages">Contact Messages</div>
                </a>
            </li>
        </ul>
    </li>




    <!-- Users -->
   @if(auth()->user()->hasPermission('users') && Route::has('users.index'))
    <li class="menu-item {{ menuItemActive(['users.*'], 'open active') }}">

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

            @if(auth()->user()->hasPermission('users') && Route::has('users.index'))
            <li class="menu-item {{ menuItemActive(['users.index']) }}">
                <a href="{{ route('users.index') }}" class="menu-link">
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

     <!-- Ecommerce -->
    <li class="menu-item">

        <a href="javascript:void(0);" class="menu-link">
            <i class="menu-icon icon-base ti tabler-settings"></i>
            <div data-i18n="Settings">Settings</div>
        </a>
    </li>


</ul>

</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
