<nav
class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
id="layout-navbar">
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0   d-xl-none ">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    <i class="icon-base ti tabler-menu-2 icon-md"></i>
    </a>
</div>

<div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
    <div class="nav-item navbar-search-wrapper px-md-0 px-2 mb-0">
        <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
        <span class="d-inline-block text-body-secondary fw-normal" id="autocomplete"></span>
        </a>
    </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">

    <!-- Style Switcher -->
    <li class="nav-item dropdown">
        <a
        class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
        id="nav-theme"
        href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
        <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
        <li>
            <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="false">
            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3" data-icon="device-desktop-analytics"></i>System</span>
            </button>
        </li>
        </ul>
    </li>
    <!-- / Style Switcher-->

    <!-- Quick links  -->
    <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
        <a
        class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
        href="javascript:void(0);"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <i class="icon-base ti tabler-layout-grid-add icon-22px text-heading"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
        <div class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Shortcuts</h6>
            </div>
        </div>
        <div class="dropdown-shortcuts-list scrollable-container">
            <div class="row row-bordered overflow-visible g-0">
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-smart-home icon-26px text-heading"></i>
                </span>
                <a href="{{ route('dashboard') }}" class="stretched-link">Dashboard</a>
                <small>Overview</small>
            </div>
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-garden-cart icon-26px text-heading"></i>
                </span>
                <a href="{{ route('orders.index') }}" class="stretched-link">Orders</a>
                <small>Manage Orders</small>
            </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-brand-databricks icon-26px text-heading"></i>
                </span>
                <a href="{{ route('products.index') }}" class="stretched-link">Products</a>
                <small>All Products</small>
            </div>
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-user icon-26px text-heading"></i>
                </span>
                <a href="{{ route('users.index') }}" class="stretched-link">Users</a>
                <small>Manage Users</small>
            </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-file-dollar icon-26px text-heading"></i>
                </span>
                <a href="{{ route('invoices.index') }}" class="stretched-link">Invoices</a>
                <small>Manage Invoices</small>
            </div>
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-building-store icon-26px text-heading"></i>
                </span>
                <a href="{{ route('sellers.index') }}" class="stretched-link">Sellers</a>
                <small>All Sellers</small>
            </div>
            </div>
            <div class="row row-bordered overflow-visible g-0">
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-lifebuoy icon-26px text-heading"></i>
                </span>
                <a href="{{ route('support-tickets.index') }}" class="stretched-link">Tickets</a>
                <small>Support</small>
            </div>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('admin'))
            <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                <i class="icon-base ti tabler-settings icon-26px text-heading"></i>
                </span>
                <a href="{{ route('settings.general') }}" class="stretched-link">Settings</a>
                <small>Site Settings</small>
            </div>
            @endif
            </div>
        </div>
        </div>
    </li>
    <!-- /Quick links -->

    <!-- Notification -->
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a
        class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
        href="javascript:void(0);"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <span class="position-relative">
            <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
            <span class="badge rounded-pill bg-danger badge-dot badge-notifications border d-none" id="notification-badge"></span>
        </span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end p-0">
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Notifications</h6>
            <div class="d-flex align-items-center h6 mb-0">
                <span class="badge bg-label-primary me-2" id="notification-count-badge" style="display:none"></span>
                <a
                href="javascript:void(0)"
                class="dropdown-notifications-all p-2 btn btn-icon"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Mark all as read"
                id="mark-all-read-btn"
                ><i class="icon-base ti tabler-mail-opened text-heading"></i
                ></a>
            </div>
            </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush" id="notification-list">
            <li class="list-group-item text-center py-4 text-muted" id="notification-loading">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2">Loading...</span>
            </li>
            </ul>
        </li>
        <li class="border-top">
            <div class="d-grid p-4">
            <a class="btn btn-primary btn-sm d-flex" href="{{ route('notifications.index') }}">
                <small class="align-middle">View all notifications</small>
            </a>
            </div>
        </li>
        </ul>
    </li>
    <!--/ Notification -->

    <!-- User -->
    @php
        $__headerAvatar = Auth::user()->profile?->avatar;
        $__headerInitials = strtoupper(substr(Auth::user()->name, 0, 1)) . strtoupper(substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1));
    @endphp
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a
        class="nav-link dropdown-toggle hide-arrow p-0"
        href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
            @if($__headerAvatar)
                <img src="{{ asset($__headerAvatar) }}" alt class="rounded-circle" />
            @else
                <span class="avatar-initial rounded-circle bg-label-primary">{{ $__headerInitials }}</span>
            @endif
        </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item mt-0" href="{{ route('profile.edit') }}">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                    @if($__headerAvatar)
                        <img src="{{ asset($__headerAvatar) }}" alt class="rounded-circle" />
                    @else
                        <span class="avatar-initial rounded-circle bg-label-primary">{{ $__headerInitials }}</span>
                    @endif
                </div>
                </div>
                <div class="flex-grow-1">
                <h6 class="mb-0">{{ ucwords(Auth::user()->name) }}</h6>
                <small class="text-body-secondary">{{ Auth::user()->roles->first()?->name ?? 'Admin' }}</small>
                </div>
            </div>
            </a>
        </li>
        <li>
            <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
            <i class="icon-base ti tabler-user me-3 icon-md"></i><span class="align-middle">My Profile</span>
            </a>
        </li>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('admin'))
        <li>
            <a class="dropdown-item" href="{{ route('settings.general') }}">
            <i class="icon-base ti tabler-settings me-3 icon-md"></i><span class="align-middle">Settings</span>
            </a>
        </li>
        @endif
        <li>
            <div class="d-grid px-2 pt-2 pb-1">
            <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <small class="align-middle">Logout</small>
                <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            </div>
        </li>
        </ul>
    </li>
    <!--/ User -->
    </ul>
</div>

{{-- Notification AJAX URLs --}}
<script>
    window._notificationUrls = {
        fetch: '{{ route("header.notifications") }}',
        markRead: '{{ route("header.notifications.read", ":id") }}',
        markAllRead: '{{ route("header.notifications.read-all") }}',
    };
</script>
</nav>
