@extends('layouts.app')
@section('title', 'Edit User — ' . $user->name)

@push('page-css')
<style>
    .user-hero {
        background: linear-gradient(135deg, #7367f0 0%, #9e95f5 50%, #ce9ffc 100%);
        border-radius: .5rem .5rem 0 0;
        padding: 1rem 1.25rem 2.75rem;
        position: relative;
        overflow: hidden;
        min-height: 100px;
    }
    .user-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .user-hero-badges {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: .375rem;
        flex-wrap: wrap;
    }
    .user-hero-avatar {
        width: 84px;
        height: 84px;
        border: 4px solid #fff;
        border-radius: 50%;
        margin-top: -42px;
        box-shadow: 0 4px 14px rgba(0,0,0,.12);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .user-hero-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .user-hero-avatar .initials {
        font-size: 1.35rem;
        font-weight: 700;
        color: #7367f0;
        background: #ede9fe;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        border: 2px solid #fff;
        position: absolute;
        bottom: 4px;
        right: 4px;
        box-shadow: 0 0 0 2px #fff;
    }
    .stat-mini {
        text-align: center;
        padding: .75rem .5rem;
        border-radius: .375rem;
        flex: 1;
    }
    .stat-mini .stat-val {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
    }
    .stat-mini .stat-lbl {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: .25rem;
    }
    .info-row {
        display: flex;
        align-items: center;
        padding: .6rem 0;
        border-bottom: 1px solid rgba(0,0,0,.04);
    }
    .info-row:last-child { border-bottom: none; }
    .info-row .info-icon {
        width: 32px;
        height: 32px;
        border-radius: .375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: .75rem;
        font-size: .85rem;
    }
    .info-row .info-key { color: #8b8b9a; font-size: .8125rem; }
    .info-row .info-val { font-weight: 500; margin-left: auto; font-size: .8125rem; }
    .addr-card {
        border-radius: .5rem;
        padding: 1rem;
        height: 100%;
        transition: box-shadow .2s, border-color .2s;
        border: 1px solid rgba(0,0,0,.08);
    }
    .addr-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }
    .addr-card.is-default { border-color: #7367f0; border-width: 2px; }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">
                <a href="{{ route('users.index') }}" class="text-muted me-1"><i class="icon-base ti tabler-arrow-left icon-md"></i></a>
                Edit User
            </h4>
            <p class="text-muted mb-0">Manage account, roles, permissions and view profile details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-label-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back
            </a>
            <button type="submit" form="user-form" class="btn btn-primary">
                <i class="icon-base ti tabler-device-floppy me-1"></i> Save Changes
            </button>
        </div>
    </div>

    @php
        $initials = strtoupper(substr($user->name, 0, 1)) . strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1));
        $avatar = $user->profile?->avatar;
    @endphp

    <form method="POST" action="{{ route('users.update', $user->id) }}" id="user-form">
        @csrf
        @method('PUT')

        <div class="row">
            {{-- Left Column --}}
            <div class="col-xl-4 col-lg-5">

                {{-- User Hero Card --}}
                <div class="card mb-4 overflow-hidden">
                    <div class="user-hero">
                        <div class="user-hero-badges">
                            <span class="badge bg-white bg-opacity-25 text-black" style="font-size:.65rem">ID #{{ $user->id }}</span>
                            <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}" style="font-size:.6rem">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($user->email_verified_at)
                                <span class="badge bg-info" style="font-size:.6rem">Verified</span>
                            @endif
                            @if($user->is_verified)
                                <span class="badge bg-warning" style="font-size:.6rem">KYC</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="d-flex flex-column align-items-center">
                            <div class="user-hero-avatar position-relative">
                                @if($avatar)
                                    <img src="{{ asset($avatar) }}" alt="{{ $user->name }}">
                                @else
                                    <span class="initials">{{ $initials }}</span>
                                @endif
                                <span class="status-dot {{ $user->is_active ? 'bg-success' : 'bg-danger' }}"></span>
                            </div>
                            <h5 class="mt-2 mb-0">{{ $user->name }}</h5>
                            <small class="text-muted">{{ $user->email }}</small>
                            <div class="d-flex gap-1 flex-wrap justify-content-center mt-2">
                                @forelse($user->roles as $role)
                                    @php
                                        $rc = match($role->name) {
                                            'superadmin' => 'bg-label-danger',
                                            'admin'      => 'bg-label-warning',
                                            'seller'     => 'bg-label-primary',
                                            'customer'   => 'bg-label-success',
                                            default      => 'bg-label-info',
                                        };
                                    @endphp
                                    <span class="badge {{ $rc }}">{{ ucfirst($role->name) }}</span>
                                @empty
                                    <span class="badge bg-label-secondary">No Role</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Mini Stats --}}
                        <div class="d-flex gap-2 mt-3 pt-3 border-top">
                            <div class="stat-mini bg-label-primary">
                                <div class="stat-val text-primary">{{ $user->orders()->count() }}</div>
                                <div class="stat-lbl text-muted">Orders</div>
                            </div>
                            <div class="stat-mini bg-label-success">
                                <div class="stat-val text-success">{{ format_currency($user->wallet?->balance ?? 0) }}</div>
                                <div class="stat-lbl text-muted">Wallet</div>
                            </div>
                            <div class="stat-mini bg-label-info">
                                <div class="stat-val text-info">{{ $user->addresses->count() }}</div>
                                <div class="stat-lbl text-muted">Addresses</div>
                            </div>
                            <div class="stat-mini bg-label-danger">
                                <div class="stat-val text-danger">{{ $user->wishlist()->count() }}</div>
                                <div class="stat-lbl text-muted">Wishlist</div>
                            </div>
                        </div>

                        @if($user->seller)
                        <a href="{{ route('sellers.edit', $user->seller->id) }}" class="btn btn-label-success btn-sm w-100 mt-3">
                            <i class="ti tabler-building-store me-1"></i> View Seller Store
                            <i class="ti tabler-external-link ms-1"></i>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Account Details --}}
                <div class="card mb-4">
                    <div class="card-header pb-2">
                        <h6 class="card-title mb-0"><i class="icon-base ti tabler-info-circle me-2 text-primary"></i>Account Details</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-at"></i></div>
                            <span class="info-key">Username</span>
                            <span class="info-val">{{ $user->username ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-calendar"></i></div>
                            <span class="info-key">Joined</span>
                            <span class="info-val">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-clock"></i></div>
                            <span class="info-key">Last Updated</span>
                            <span class="info-val">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                        @if($user->profile?->last_login_at)
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-login"></i></div>
                            <span class="info-key">Last Login</span>
                            <span class="info-val">{{ \Carbon\Carbon::parse($user->profile->last_login_at)->diffForHumans() }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Profile Info (read-only) --}}
                @if($user->profile)
                <div class="card mb-4">
                    <div class="card-header pb-2">
                        <h6 class="card-title mb-0"><i class="icon-base ti tabler-id me-2 text-info"></i>Profile Info</h6>
                    </div>
                    <div class="card-body pt-0">
                        @if($user->profile->full_name)
                        <div class="info-row">
                            <div class="info-icon bg-label-primary"><i class="ti tabler-user"></i></div>
                            <span class="info-key">Full Name</span>
                            <span class="info-val">{{ $user->profile->full_name }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-icon bg-label-success"><i class="ti tabler-phone"></i></div>
                            <span class="info-key">Phone</span>
                            <span class="info-val">{{ $user->profile->phone ?: '—' }}</span>
                        </div>
                        @if($user->profile->alternate_phone)
                        <div class="info-row">
                            <div class="info-icon bg-label-success"><i class="ti tabler-phone-plus"></i></div>
                            <span class="info-key">Alt Phone</span>
                            <span class="info-val">{{ $user->profile->alternate_phone }}</span>
                        </div>
                        @endif
                        @if($user->profile->gender)
                        <div class="info-row">
                            <div class="info-icon bg-label-info"><i class="ti tabler-gender-bigender"></i></div>
                            <span class="info-key">Gender</span>
                            <span class="info-val">{{ ucfirst($user->profile->gender) }}</span>
                        </div>
                        @endif
                        @if($user->profile->dob)
                        <div class="info-row">
                            <div class="info-icon bg-label-warning"><i class="ti tabler-cake"></i></div>
                            <span class="info-key">Date of Birth</span>
                            <span class="info-val">{{ \Carbon\Carbon::parse($user->profile->dob)->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($user->profile->company)
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-building"></i></div>
                            <span class="info-key">Company</span>
                            <span class="info-val">{{ $user->profile->company }}</span>
                        </div>
                        @endif
                        @if($user->profile->tax_id)
                        <div class="info-row">
                            <div class="info-icon bg-label-secondary"><i class="ti tabler-receipt-tax"></i></div>
                            <span class="info-key">Tax ID</span>
                            <span class="info-val">{{ $user->profile->tax_id }}</span>
                        </div>
                        @endif
                        <div class="info-row">
                            <div class="info-icon bg-label-{{ $user->profile->newsletter_subscribed ? 'success' : 'secondary' }}">
                                <i class="ti tabler-mail"></i>
                            </div>
                            <span class="info-key">Newsletter</span>
                            <span class="info-val">
                                <span class="badge {{ $user->profile->newsletter_subscribed ? 'bg-label-success' : 'bg-label-secondary' }}">
                                    {{ $user->profile->newsletter_subscribed ? 'Subscribed' : 'No' }}
                                </span>
                            </span>
                        </div>
                        @if($user->profile->preferred_currency || $user->profile->preferred_language)
                        <div class="d-flex gap-2 mt-2 pt-2 border-top">
                            @if($user->profile->preferred_currency)
                            <span class="badge bg-label-primary"><i class="ti tabler-currency-dollar me-1"></i>{{ $user->profile->preferred_currency }}</span>
                            @endif
                            @if($user->profile->preferred_language)
                            <span class="badge bg-label-info"><i class="ti tabler-language me-1"></i>{{ strtoupper($user->profile->preferred_language) }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Account Status --}}
                <div class="card mb-4">
                    <div class="card-header pb-2">
                        <h6 class="card-title mb-0"><i class="icon-base ti tabler-shield-check me-2 text-success"></i>Account Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-0">Active Account</h6>
                                <small class="text-muted">User can login and access the platform</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;"
                                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="mb-0">Verified (KYC)</h6>
                                <small class="text-muted">Identity / seller verification status</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="is_verified" value="0">
                                <input type="checkbox" name="is_verified" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;"
                                    {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0">Email Verified</h6>
                                <small class="text-muted">
                                    @if($user->email_verified_at)
                                        Verified on {{ $user->email_verified_at->format('M d, Y H:i') }}
                                    @else
                                        Not verified yet
                                    @endif
                                </small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="email_verified" value="0">
                                <input type="checkbox" name="email_verified" value="1" class="form-check-input"
                                    style="width:3rem;height:1.5rem;"
                                    {{ old('email_verified', $user->email_verified_at ? 1 : 0) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Danger Zone --}}
                <div class="card border border-danger mb-4">
                    <div class="card-header pb-2">
                        <h6 class="card-title mb-0 text-danger"><i class="icon-base ti tabler-alert-triangle me-2"></i>Danger Zone</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Permanently delete this user account. This action cannot be undone.</p>
                        <button type="button" class="btn btn-danger w-100" id="btn-delete-user"
                            data-url="{{ route('users.destroy', $user->id) }}"
                            {{ $user->isSuperAdmin() ? 'disabled' : '' }}>
                            <i class="icon-base ti tabler-trash me-1"></i> Delete User
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Personal Information --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-user-edit me-2"></i>Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                        value="{{ old('username', $user->username) }}" placeholder="username">
                                </div>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Change Password --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-lock me-2"></i>Change Password</h5>
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;">
                                    <span class="input-group-text cursor-pointer toggle-password" data-target="password">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;">
                                    <span class="input-group-text cursor-pointer toggle-password" data-target="password_confirmation">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Roles --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-shield me-2"></i>Roles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($roles as $role)
                                @php
                                    $roleIcon = match($role->name) {
                                        'superadmin' => 'tabler-crown',
                                        'admin'      => 'tabler-shield-check',
                                        'seller'     => 'tabler-building-store',
                                        'customer'   => 'tabler-user',
                                        default      => 'tabler-users',
                                    };
                                    $roleColor = match($role->name) {
                                        'superadmin' => 'danger',
                                        'admin'      => 'warning',
                                        'seller'     => 'primary',
                                        'customer'   => 'success',
                                        default      => 'info',
                                    };
                                    $isChecked = in_array($role->id, old('roles', $user->roles->pluck('id')->toArray()));
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label class="d-flex align-items-center border rounded p-3 cursor-pointer role-label {{ $isChecked ? 'border-'.$roleColor.' bg-label-'.$roleColor : '' }}"
                                        for="role-{{ $role->id }}">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                                            class="form-check-input me-3 role-checkbox" data-color="{{ $roleColor }}"
                                            {{ $isChecked ? 'checked' : '' }}>
                                        <div>
                                            <i class="icon-base ti {{ $roleIcon }} me-1 text-{{ $roleColor }}"></i>
                                            <span class="fw-medium">{{ ucfirst($role->label ?? $role->name) }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Permissions --}}
                @if($permissions->count())
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-key me-2"></i>Direct Permissions</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-text-primary" id="check-all-perms">Select All</button>
                            <button type="button" class="btn btn-sm btn-text-secondary" id="uncheck-all-perms">Deselect All</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($permissions as $permission)
                                <div class="col-xl-4 col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                            id="perm-{{ $permission->id }}" class="form-check-input perm-checkbox"
                                            {{ in_array($permission->id, old('permissions', $user->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label for="perm-{{ $permission->id }}" class="form-check-label">
                                            {{ $permission->label ?? ucfirst($permission->name) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Saved Addresses (read-only) --}}
                @if($user->addresses->count())
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="icon-base ti tabler-map-pin me-2"></i>Saved Addresses</h5>
                        <span class="badge bg-label-info rounded-pill">{{ $user->addresses->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($user->addresses as $addr)
                            <div class="col-md-6">
                                <div class="addr-card {{ $addr->is_default ? 'is-default' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle {{ $addr->is_default ? 'bg-primary' : 'bg-label-secondary' }}" style="width:30px;height:30px;flex-shrink:0">
                                                <i class="ti {{ $addr->label === 'Home' ? 'tabler-home' : ($addr->label === 'Office' ? 'tabler-briefcase' : 'tabler-map-pin') }} {{ $addr->is_default ? 'text-white' : '' }}" style="font-size:.8rem"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold d-block" style="font-size:.8125rem;line-height:1.2">{{ $addr->label ?? 'Address' }}</span>
                                                @if($addr->is_default)
                                                    <span class="badge bg-primary" style="font-size:.55rem">Default</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="badge bg-label-{{ $addr->type === 'billing' ? 'warning' : ($addr->type === 'shipping' ? 'info' : 'success') }}" style="font-size:.6rem">
                                            {{ ucfirst($addr->type) }}
                                        </span>
                                    </div>
                                    <p class="mb-1" style="font-size:.8rem">{{ $addr->full_name }}{{ $addr->company ? ' · ' . $addr->company : '' }}</p>
                                    <p class="text-muted mb-0" style="font-size:.775rem"><i class="ti tabler-map-pin me-1" style="font-size:.7rem"></i>{{ $addr->formatted }}</p>
                                    @if($addr->phone)
                                    <p class="text-muted mb-0 mt-1" style="font-size:.775rem"><i class="ti tabler-phone me-1" style="font-size:.7rem"></i>{{ $addr->phone }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </form>

@endsection

@push('page-js')
<script>
$(function () {
    $('.toggle-password').on('click', function () {
        var target = $('#' + $(this).data('target'));
        var icon = $(this).find('i');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('tabler-eye-off').addClass('tabler-eye');
        } else {
            target.attr('type', 'password');
            icon.removeClass('tabler-eye').addClass('tabler-eye-off');
        }
    });

    $('.role-checkbox').on('change', function () {
        var label = $(this).closest('.role-label');
        var color = $(this).data('color');
        if (this.checked) {
            label.addClass('border-' + color + ' bg-label-' + color);
        } else {
            label.removeClass('border-' + color + ' bg-label-' + color);
        }
    });

    $('#check-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', true); });
    $('#uncheck-all-perms').on('click', function () { $('.perm-checkbox').prop('checked', false); });

    $('#btn-delete-user').on('click', function () {
        var url = $(this).data('url');
        Swal.fire({
            title: 'Delete this user?',
            text: 'This action is permanent and cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#d33',
        }).then(function (result) {
            if (!result.isConfirmed) return;
            var form = $('<form>', { method: 'POST', action: url });
            form.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
            form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
            $('body').append(form);
            form.submit();
        });
    });
});
</script>
@endpush
