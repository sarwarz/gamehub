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
            <span class="app-brand-text demo menu-text fw-bold ms-3">Vuexy</span>
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

    <!-- Products Group -->
    @if(
        auth()->user()->hasPermission('products') ||
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
        'products.*','categories.*','platforms.*','types.*',
        'regions.*','languages.*','workson.*','developers.*','publishers.*'
    ], 'open active') }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon icon-base ti tabler-brand-databricks"></i>
            <div data-i18n="Products">Products</div>
        </a>
        <ul class="menu-sub">
            @if(auth()->user()->hasPermission('products') && Route::has('products.index'))
            <li class="menu-item {{ menuItemActive(['products.index']) }}">
                <a href="{{ route('products.index') }}" class="menu-link">
                    <div data-i18n="Products">Products</div>
                </a>
            </li>
            @endif

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

    <!-- Orders -->
    @if(auth()->user()->hasPermission('orders') && Route::has('orders.index'))
    <li class="menu-item {{ menuItemActive(['orders.*']) }}">
        <a href="{{ route('orders.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-sort-ascending-shapes"></i>
            <div data-i18n="Order">Orders</div>
        </a>
    </li>
    @endif

    <!-- Sellers -->
    @if(auth()->user()->hasPermission('sellers') && Route::has('sellers.index'))
    <li class="menu-item {{ menuItemActive(['sellers.*']) }}">
        <a href="{{ route('sellers.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-users"></i>
            <div data-i18n="Sellers">Sellers</div>
        </a>
    </li>
    @endif

    <!-- Offers -->
    @if(auth()->user()->hasPermission('seller-offers') && Route::has('seller-offers.index'))
    <li class="menu-item {{ menuItemActive(['seller-offers.*']) }}">
        <a href="{{ route('seller-offers.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-shopping-bag-edit"></i>
            <div data-i18n="Offers">Offers</div>
        </a>
    </li>
    @endif

    <!-- Currencies -->
    @if(auth()->user()->hasPermission('currencies') && Route::has('currencies.index'))
    <li class="menu-item {{ menuItemActive(['currencies.*']) }}">
        <a href="{{ route('currencies.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-adjustments-dollar"></i>
            <div data-i18n="Currencies">Currencies</div>
        </a>
    </li>
    @endif

    <!-- Users -->
    @if(auth()->user()->hasPermission('users') && Route::has('users.index'))
    <li class="menu-item {{ menuItemActive(['users.*']) }}">
        <a href="{{ route('users.index') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-users"></i>
            <div data-i18n="Users">Users</div>
        </a>
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
</ul>

</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
        <i class="ti tabler-menu icon-base"></i>
        <i class="ti tabler-chevron-right icon-base"></i>
    </a>
</div>
