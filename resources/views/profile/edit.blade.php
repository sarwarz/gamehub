@extends('layouts.app')
@section('title', 'My Profile')

@push('page-css')
<style>
    #profileTabContent { padding: 0 !important; }
    .profile-banner {
        background: linear-gradient(135deg, #696cff 0%, #7367f0 40%, #8c57db 100%);
        border-radius: .5rem .5rem 0 0;
        height: 180px;
        position: relative;
        overflow: hidden;
    }
    .profile-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .profile-avatar-wrap {
        position: relative;
        margin-top: -60px;
        z-index: 2;
    }
    .profile-avatar-wrap .avatar-initial {
        width: 110px;
        height: 110px;
        font-size: 2.2rem;
        border: 4px solid var(--bs-body-bg);
        box-shadow: 0 4px 18px rgba(0,0,0,.12);
    }
    .profile-avatar-wrap .avatar-img {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--bs-body-bg);
        box-shadow: 0 4px 18px rgba(0,0,0,.12);
    }
    .profile-tab-nav .nav-link {
        font-weight: 500;
        padding: .65rem 1.25rem;
        border-radius: .375rem;
        color: var(--bs-body-color);
        transition: all .2s;
    }
    .profile-tab-nav .nav-link:hover { background: rgba(105,108,255,.06); color: #696cff; }
    .profile-tab-nav .nav-link.active {
        background: rgba(105,108,255,.12);
        color: #696cff;
        box-shadow: none;
    }
    .profile-tab-nav .nav-link i { font-size: 1.15rem; }
    .info-item { display: flex; align-items: start; gap: .75rem; padding: .6rem 0; }
    .info-item:not(:last-child) { border-bottom: 1px solid var(--bs-border-color); }
    .info-item .info-icon { width: 34px; height: 34px; border-radius: .375rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
    .info-item .info-label { font-size: .75rem; color: var(--bs-secondary-color); margin-bottom: 1px; }
    .info-item .info-value { font-weight: 500; font-size: .875rem; }
    .section-icon { width: 40px; height: 40px; border-radius: .5rem; display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
    .form-floating-icon { position: relative; }
    .form-floating-icon .form-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--bs-secondary-color); z-index: 3; font-size: 1.1rem; }
    .form-floating-icon .form-control,
    .form-floating-icon .form-select { padding-left: 2.5rem; }
    .profile-stat { text-align: center; padding: .5rem; }
    .profile-stat .stat-value { font-size: 1.25rem; font-weight: 700; }
    .profile-stat .stat-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .5px; color: var(--bs-secondary-color); }
    .completion-bar { height: 6px; border-radius: 3px; background: var(--bs-border-color); overflow: hidden; }
    .completion-bar .fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, #696cff, #7367f0); transition: width .6s ease; }
    .pref-toggle { display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--bs-border-color); border-radius: .5rem; transition: border-color .2s; }
    .pref-toggle:hover { border-color: #696cff; }
    .avatar-upload-zone {
        border: 2px dashed var(--bs-border-color);
        border-radius: .75rem;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all .25s;
        background: var(--bs-body-bg);
        position: relative;
    }
    .avatar-upload-zone:hover,
    .avatar-upload-zone.dragover {
        border-color: #696cff;
        background: rgba(105,108,255,.04);
    }
    .avatar-upload-zone .upload-placeholder { color: var(--bs-secondary-color); }
    .avatar-upload-zone .upload-placeholder i { font-size: 2.5rem; display: block; margin-bottom: .5rem; color: #696cff; }
    .avatar-preview-container { position: relative; display: inline-block; }
    .avatar-preview-container img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--bs-border-color);
    }
    .avatar-preview-container .remove-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: #ff4c51;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .75rem;
        transition: transform .15s;
        box-shadow: 0 2px 6px rgba(0,0,0,.2);
    }
    .avatar-preview-container .remove-btn:hover { transform: scale(1.1); }
    .address-card { transition: border-color .2s, box-shadow .2s; }
    .address-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .address-card.border-primary { border-width: 2px !important; }
</style>
@endpush

@section('content')
@php
    $checkFields = [
        'first_name','last_name','avatar','dob','gender','phone','alternate_phone',
        'company','tax_id','preferred_currency','preferred_language'
    ];
    $filledCount = 0;
    foreach ($checkFields as $f) {
        if (!empty($profile->$f)) $filledCount++;
    }
    if (!empty($user->name)) $filledCount++;
    if (!empty($user->email)) $filledCount++;
    $totalFields = count($checkFields) + 2;
    $completionPct = round(($filledCount / $totalFields) * 100);
@endphp

{{-- Banner + Avatar + Tabs --}}
<div class="card mb-4">
    <div class="profile-banner"></div>
    <div class="card-body pb-0">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-end gap-3">
            <div class="profile-avatar-wrap text-center text-md-start">
                @if($profile->avatar)
                    <img src="{{ asset($profile->avatar) }}" alt="{{ $user->name }}" class="avatar-img">
                @else
                    <span class="avatar-initial rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1)) }}
                    </span>
                @endif
            </div>
            <div class="flex-grow-1 pt-md-2">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2">
                    <div>
                        <h4 class="mb-1">{{ ucwords($user->name) }}</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-label-primary"><i class="ti tabler-shield-check me-1" style="font-size:.7rem"></i>{{ $user->roles->first()?->label ?? $user->roles->first()?->name ?? 'User' }}</span>
                            @if($user->email_verified_at)
                                <span class="badge bg-label-success"><i class="ti tabler-circle-check me-1" style="font-size:.7rem"></i>Verified</span>
                            @else
                                <span class="badge bg-label-warning"><i class="ti tabler-alert-circle me-1" style="font-size:.7rem"></i>Unverified</span>
                            @endif
                            @if($user->is_verified)
                                <span class="badge bg-label-info"><i class="ti tabler-rosette-discount-check me-1" style="font-size:.7rem"></i>KYC</span>
                            @endif
                            @if($user->is_active)
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-none d-md-flex align-items-center gap-4">
                        <div class="profile-stat">
                            <div class="stat-value text-primary">{{ $user->created_at?->format('M Y') }}</div>
                            <div class="stat-label">Joined</div>
                        </div>
                        <div class="vr" style="height:36px"></div>
                        <div class="profile-stat">
                            <div class="stat-value text-primary">{{ $completionPct }}%</div>
                            <div class="stat-label">Profile</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav profile-tab-nav gap-1 mt-4 border-0" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-account" data-bs-toggle="tab" data-bs-target="#pane-account" type="button" role="tab">
                    <i class="ti tabler-user me-1"></i> Account
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-personal" data-bs-toggle="tab" data-bs-target="#pane-personal" type="button" role="tab">
                    <i class="ti tabler-id me-1"></i> Personal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-address" data-bs-toggle="tab" data-bs-target="#pane-address" type="button" role="tab">
                    <i class="ti tabler-map-pin me-1"></i> Addresses
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-preferences" data-bs-toggle="tab" data-bs-target="#pane-preferences" type="button" role="tab">
                    <i class="ti tabler-settings me-1"></i> Preferences
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-security" data-bs-toggle="tab" data-bs-target="#pane-security" type="button" role="tab">
                    <i class="ti tabler-lock me-1"></i> Security
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="row">
    {{-- Left Sidebar --}}
    <div class="col-xl-4 col-lg-5 order-1 order-lg-0">

        {{-- Profile Completion --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="mb-0">Profile Completion</h6>
                    <span class="fw-bold text-primary">{{ $completionPct }}%</span>
                </div>
                <div class="completion-bar mb-3">
                    <div class="fill" style="width: {{ $completionPct }}%"></div>
                </div>
                @if($completionPct < 100)
                    <small class="text-muted">Fill in all fields to complete your profile.</small>
                @else
                    <small class="text-success"><i class="ti tabler-circle-check me-1"></i>Your profile is complete!</small>
                @endif
            </div>
        </div>

        {{-- About / Quick Info --}}
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h6 class="card-title mb-0"><i class="ti tabler-info-circle me-2 text-primary"></i>About</h6>
            </div>
            <div class="card-body pt-0">
                <div class="info-item">
                    <div class="info-icon bg-label-primary"><i class="ti tabler-user"></i></div>
                    <div>
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $profile->full_name ?: ucwords($user->name) }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-label-info"><i class="ti tabler-mail"></i></div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-label-success"><i class="ti tabler-phone"></i></div>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $profile->phone ?: '—' }}</div>
                    </div>
                </div>
                @if($profile->gender)
                <div class="info-item">
                    <div class="info-icon bg-label-primary"><i class="ti tabler-gender-bigender"></i></div>
                    <div>
                        <div class="info-label">Gender</div>
                        <div class="info-value">{{ ucfirst($profile->gender) }}</div>
                    </div>
                </div>
                @endif
                @if($profile->dob)
                <div class="info-item">
                    <div class="info-icon bg-label-info"><i class="ti tabler-cake"></i></div>
                    <div>
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($profile->dob)->format('d M Y') }}</div>
                    </div>
                </div>
                @endif
                @if($profile->company)
                <div class="info-item">
                    <div class="info-icon bg-label-warning"><i class="ti tabler-building"></i></div>
                    <div>
                        <div class="info-label">Company</div>
                        <div class="info-value">{{ $profile->company }}</div>
                    </div>
                </div>
                @endif
                <div class="info-item">
                    <div class="info-icon bg-label-primary"><i class="ti tabler-calendar"></i></div>
                    <div>
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ $user->created_at?->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Roles --}}
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h6 class="card-title mb-0"><i class="ti tabler-shield me-2 text-primary"></i>Roles</h6>
            </div>
            <div class="card-body pt-0">
                @forelse($user->roles as $role)
                    @php
                        $rc = match($role->name) {
                            'superadmin' => 'danger', 'admin' => 'warning', 'support_agent' => 'info',
                            'seller' => 'primary', 'customer' => 'success', default => 'secondary',
                        };
                        $ri = match($role->name) {
                            'superadmin' => 'tabler-crown', 'admin' => 'tabler-shield-check', 'support_agent' => 'tabler-headset',
                            'seller' => 'tabler-building-store', 'customer' => 'tabler-user', default => 'tabler-users',
                        };
                    @endphp
                    <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}">
                        <span class="info-icon bg-label-{{ $rc }}"><i class="ti {{ $ri }}"></i></span>
                        <div>
                            <span class="fw-medium">{{ $role->label ?? ucfirst($role->name) }}</span>
                            <span class="badge bg-label-{{ $role->type === 'internal' ? 'primary' : 'warning' }}" style="font-size:.6rem; vertical-align: middle;">{{ ucfirst($role->type ?? 'N/A') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No roles assigned.</p>
                @endforelse
            </div>
        </div>

        {{-- Preferences Summary --}}
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h6 class="card-title mb-0"><i class="ti tabler-adjustments me-2 text-primary"></i>Preferences</h6>
            </div>
            <div class="card-body pt-0">
                <div class="info-item">
                    <div class="info-icon bg-label-success"><i class="ti tabler-currency-dollar"></i></div>
                    <div>
                        <div class="info-label">Currency</div>
                        <div class="info-value">{{ $profile->preferred_currency ?: 'USD' }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-label-info"><i class="ti tabler-language"></i></div>
                    <div>
                        <div class="info-label">Language</div>
                        <div class="info-value">{{ strtoupper($profile->preferred_language ?: 'en') }}</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon bg-label-{{ $profile->newsletter_subscribed ? 'success' : 'secondary' }}"><i class="ti tabler-mail-forward"></i></div>
                    <div>
                        <div class="info-label">Newsletter</div>
                        <div class="info-value">{{ $profile->newsletter_subscribed ? 'Subscribed' : 'Not subscribed' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($profile->last_login_at)
        <div class="card mb-4">
            <div class="card-header pb-2">
                <h6 class="card-title mb-0"><i class="ti tabler-clock me-2 text-primary"></i>Last Login</h6>
            </div>
            <div class="card-body pt-0">
                <div class="info-item">
                    <div class="info-icon bg-label-success"><i class="ti tabler-calendar-time"></i></div>
                    <div>
                        <div class="info-label">Date & Time</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($profile->last_login_at)->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
                @if($profile->last_login_ip)
                <div class="info-item">
                    <div class="info-icon bg-label-warning"><i class="ti tabler-world"></i></div>
                    <div>
                        <div class="info-label">IP Address</div>
                        <div class="info-value">{{ $profile->last_login_ip }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right Content (Tabs) --}}
    <div class="col-xl-8 col-lg-7 order-0 order-lg-1">

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ti tabler-circle-check me-2 fs-5"></i>
                    <strong>Profile updated successfully.</strong>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ti tabler-circle-check me-2 fs-5"></i>
                    <strong>Password updated successfully.</strong>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="tab-content" id="profileTabContent">

            {{-- ========== Account Tab ========== --}}
            <div class="tab-pane fade show active" id="pane-account" role="tabpanel">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Avatar Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-info"><i class="ti tabler-photo"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Profile Picture</h5>
                                    <small class="text-muted">Upload a photo (JPG, PNG, GIF, WebP — max 2MB)</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">
                            <div id="avatar-upload-area">
                                @if($profile->avatar)
                                    {{-- Current avatar preview --}}
                                    <div class="text-center" id="avatar-current">
                                        <div class="avatar-preview-container mb-3">
                                            <img src="{{ asset($profile->avatar) }}" alt="Avatar" id="avatar-img-preview">
                                            <span class="remove-btn" id="btn-remove-avatar" title="Remove photo">
                                                <i class="ti tabler-x"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <label for="avatar_file" class="btn btn-sm btn-label-primary mb-0" style="cursor:pointer;">
                                                <i class="ti tabler-upload me-1"></i> Change Photo
                                            </label>
                                        </div>
                                    </div>
                                @endif
                                {{-- Upload drop zone (shown when no avatar or after remove) --}}
                                <div class="avatar-upload-zone" id="avatar-dropzone" style="{{ $profile->avatar ? 'display:none' : '' }}">
                                    <div class="upload-placeholder" id="upload-placeholder">
                                        <i class="ti tabler-cloud-upload"></i>
                                        <p class="mb-1 fw-medium">Drop image here or click to upload</p>
                                        <small>JPG, PNG, GIF or WebP. Max 2MB.</small>
                                    </div>
                                    <div class="d-none" id="upload-preview-wrap">
                                        <div class="avatar-preview-container mb-3">
                                            <img src="" alt="Preview" id="avatar-new-preview">
                                            <span class="remove-btn" id="btn-clear-upload" title="Remove">
                                                <i class="ti tabler-x"></i>
                                            </span>
                                        </div>
                                        <small class="text-success"><i class="ti tabler-circle-check me-1"></i>Ready to upload</small>
                                    </div>
                                </div>
                            </div>
                            <input type="file" name="avatar_file" id="avatar_file" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp">
                            @error('avatar_file') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Account Info Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-primary"><i class="ti tabler-user-edit"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Account Information</h5>
                                    <small class="text-muted">Manage your primary account details</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-user form-icon"></i>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Full Name" required>
                                    </div>
                                    @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-mail form-icon"></i>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Email" required>
                                    </div>
                                    @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti tabler-device-floppy me-1"></i> Save Account Info
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ========== Personal Tab ========== --}}
            <div class="tab-pane fade" id="pane-personal" role="tabpanel">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">

                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-info"><i class="ti tabler-id"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Personal Details</h5>
                                    <small class="text-muted">Your personal and identity information</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="first_name">First Name</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-user form-icon"></i>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}" placeholder="First Name">
                                    </div>
                                    @error('first_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="last_name">Last Name</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-user form-icon"></i>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                               id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}" placeholder="Last Name">
                                    </div>
                                    @error('last_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="gender">Gender</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-gender-bigender form-icon"></i>
                                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" style="padding-left: 2.5rem;">
                                            <option value="">-- Select --</option>
                                            <option value="male" {{ old('gender', $profile->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender', $profile->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender', $profile->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    @error('gender') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="dob">Date of Birth</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-calendar form-icon"></i>
                                        <input type="text" class="form-control flatpickr-dob @error('dob') is-invalid @enderror"
                                               id="dob" name="dob" value="{{ old('dob', $profile->dob) }}" placeholder="Select date" readonly>
                                    </div>
                                    @error('dob') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="phone">Phone</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-phone form-icon"></i>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" placeholder="+1 (000) 000-0000">
                                    </div>
                                    @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="alternate_phone">Alternate Phone</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-phone-plus form-icon"></i>
                                        <input type="text" class="form-control @error('alternate_phone') is-invalid @enderror"
                                               id="alternate_phone" name="alternate_phone" value="{{ old('alternate_phone', $profile->alternate_phone) }}" placeholder="+1 (000) 000-0000">
                                    </div>
                                    @error('alternate_phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti tabler-device-floppy me-1"></i> Save Personal Details
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Business Card --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-warning"><i class="ti tabler-building"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Business Information</h5>
                                    <small class="text-muted">Company and tax details (optional)</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="company">Company Name</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-building form-icon"></i>
                                        <input type="text" class="form-control @error('company') is-invalid @enderror"
                                               id="company" name="company" value="{{ old('company', $profile->company) }}" placeholder="Acme Inc.">
                                    </div>
                                    @error('company') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="tax_id">Tax ID / VAT</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-receipt-tax form-icon"></i>
                                        <input type="text" class="form-control @error('tax_id') is-invalid @enderror"
                                               id="tax_id" name="tax_id" value="{{ old('tax_id', $profile->tax_id) }}" placeholder="US123456789">
                                    </div>
                                    @error('tax_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti tabler-device-floppy me-1"></i> Save Business Info
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ========== Addresses Tab ========== --}}
            <div class="tab-pane fade" id="pane-address" role="tabpanel">

                {{-- Add New Address --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="section-icon bg-label-success"><i class="ti tabler-map-pin"></i></div>
                            <div>
                                <h5 class="card-title mb-0">Saved Addresses</h5>
                                <small class="text-muted">Manage your billing &amp; shipping addresses</small>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#newAddressForm">
                            <i class="ti tabler-plus me-1"></i> Add Address
                        </button>
                    </div>

                    {{-- Collapsible New Address Form --}}
                    <div class="collapse" id="newAddressForm">
                        <div class="card-body border-top">
                            <form method="POST" action="{{ route('profile.address.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Label</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-tag form-icon"></i>
                                            <select name="label" class="form-select" style="padding-left:2.5rem">
                                                <option value="Home">Home</option>
                                                <option value="Office">Office</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-user form-icon"></i>
                                            <input type="text" name="first_name" class="form-control" placeholder="John" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-user form-icon"></i>
                                            <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Company</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-building form-icon"></i>
                                            <input type="text" name="company" class="form-control" placeholder="Company name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-phone form-icon"></i>
                                            <input type="text" name="phone" class="form-control" placeholder="+1 (000) 000-0000">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-map-pin form-icon"></i>
                                            <input type="text" name="address_line1" class="form-control" placeholder="123 Main Street" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address Line 2</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-map-2 form-icon"></i>
                                            <input type="text" name="address_line2" class="form-control" placeholder="Apt 4B, Suite 200">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-building-community form-icon"></i>
                                            <input type="text" name="city" class="form-control" placeholder="New York" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">State / Province</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-map form-icon"></i>
                                            <input type="text" name="state" class="form-control" placeholder="NY">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Postal Code</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-hash form-icon"></i>
                                            <input type="text" name="postal_code" class="form-control" placeholder="10001">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Country <span class="text-danger">*</span></label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-world form-icon"></i>
                                            <input type="text" name="country" class="form-control" placeholder="United States" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Type</label>
                                        <div class="form-floating-icon">
                                            <i class="ti tabler-category form-icon"></i>
                                            <select name="type" class="form-select" style="padding-left:2.5rem">
                                                <option value="both">Both (Billing & Shipping)</option>
                                                <option value="billing">Billing Only</option>
                                                <option value="shipping">Shipping Only</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="new_is_default">
                                            <label class="form-check-label" for="new_is_default">Set as default address</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-label-secondary" data-bs-toggle="collapse" data-bs-target="#newAddressForm">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti tabler-device-floppy me-1"></i> Save Address
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Address Cards --}}
                @forelse($addresses as $addr)
                <div class="card mb-3 address-card {{ $addr->is_default ? 'border-primary' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-sm rounded-circle d-flex align-items-center justify-content-center {{ $addr->is_default ? 'bg-primary' : 'bg-label-secondary' }}" style="width:38px;height:38px">
                                    <i class="ti {{ $addr->label === 'Home' ? 'tabler-home' : ($addr->label === 'Office' ? 'tabler-briefcase' : 'tabler-map-pin') }} {{ $addr->is_default ? 'text-white' : '' }}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">
                                        {{ $addr->label ?? 'Address' }}
                                        @if($addr->is_default)
                                            <span class="badge bg-primary ms-1" style="font-size:.65rem">Default</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ $addr->full_name }}{{ $addr->company ? ' · ' . $addr->company : '' }}</small>
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                <span class="badge bg-label-{{ $addr->type === 'billing' ? 'warning' : ($addr->type === 'shipping' ? 'info' : 'success') }}">
                                    {{ ucfirst($addr->type) }}
                                </span>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 text-muted">
                                    <i class="ti tabler-map-pin mt-1" style="font-size:.85rem"></i>
                                    <span>{{ $addr->formatted }}</span>
                                </div>
                            </div>
                            @if($addr->phone)
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 text-muted">
                                    <i class="ti tabler-phone" style="font-size:.85rem"></i>
                                    <span>{{ $addr->phone }}</span>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="d-flex gap-1">
                                @if(!$addr->is_default)
                                <form method="POST" action="{{ route('profile.address.default', $addr) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-label-primary btn-sm">
                                        <i class="ti tabler-star me-1"></i> Set Default
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-label-info btn-sm btn-edit-address"
                                        data-id="{{ $addr->id }}"
                                        data-label="{{ $addr->label }}"
                                        data-first_name="{{ $addr->first_name }}"
                                        data-last_name="{{ $addr->last_name }}"
                                        data-company="{{ $addr->company }}"
                                        data-phone="{{ $addr->phone }}"
                                        data-address_line1="{{ $addr->address_line1 }}"
                                        data-address_line2="{{ $addr->address_line2 }}"
                                        data-city="{{ $addr->city }}"
                                        data-state="{{ $addr->state }}"
                                        data-postal_code="{{ $addr->postal_code }}"
                                        data-country="{{ $addr->country }}"
                                        data-type="{{ $addr->type }}"
                                        data-is_default="{{ $addr->is_default ? '1' : '0' }}">
                                    <i class="ti tabler-pencil me-1"></i> Edit
                                </button>
                                <form method="POST" action="{{ route('profile.address.destroy', $addr) }}" onsubmit="return confirm('Delete this address?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-label-danger btn-sm">
                                        <i class="ti tabler-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-3">
                            <div class="avatar avatar-lg rounded-circle bg-label-secondary mx-auto d-flex align-items-center justify-content-center" style="width:64px;height:64px">
                                <i class="ti tabler-map-pin-off" style="font-size:1.75rem"></i>
                            </div>
                        </div>
                        <h6 class="mb-1">No Addresses Saved</h6>
                        <p class="text-muted mb-3">Add your first address for faster checkout</p>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#newAddressForm">
                            <i class="ti tabler-plus me-1"></i> Add Your First Address
                        </button>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Edit Address Modal --}}
            <div class="modal fade" id="editAddressModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form id="editAddressForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="ti tabler-pencil me-2"></i>Edit Address</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Label</label>
                                        <select name="label" id="edit_label" class="form-select">
                                            <option value="Home">Home</option>
                                            <option value="Office">Office</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Company</label>
                                        <input type="text" name="company" id="edit_company" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" id="edit_phone" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                        <input type="text" name="address_line1" id="edit_address_line1" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Address Line 2</label>
                                        <input type="text" name="address_line2" id="edit_address_line2" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" id="edit_city" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" id="edit_state" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Postal Code</label>
                                        <input type="text" name="postal_code" id="edit_postal_code" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Country <span class="text-danger">*</span></label>
                                        <input type="text" name="country" id="edit_country" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Type</label>
                                        <select name="type" id="edit_type" class="form-select">
                                            <option value="both">Both (Billing & Shipping)</option>
                                            <option value="billing">Billing Only</option>
                                            <option value="shipping">Shipping Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="edit_is_default">
                                            <label class="form-check-label" for="edit_is_default">Set as default address</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti tabler-device-floppy me-1"></i> Update Address
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========== Preferences Tab ========== --}}
            <div class="tab-pane fade" id="pane-preferences" role="tabpanel">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="name" value="{{ $user->name }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">

                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-primary"><i class="ti tabler-settings"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Regional Preferences</h5>
                                    <small class="text-muted">Set your preferred currency and language</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="preferred_currency">Preferred Currency</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-currency-dollar form-icon"></i>
                                        <select class="form-select @error('preferred_currency') is-invalid @enderror" id="preferred_currency" name="preferred_currency" style="padding-left: 2.5rem;">
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->code }}" {{ old('preferred_currency', $profile->preferred_currency ?: 'USD') === $currency->code ? 'selected' : '' }}>
                                                    {{ $currency->code }} — {{ $currency->name }} ({{ $currency->symbol }})
                                                </option>
                                            @endforeach
                                            @if($currencies->isEmpty())
                                                <option value="USD" selected>USD — US Dollar ($)</option>
                                            @endif
                                        </select>
                                    </div>
                                    @error('preferred_currency') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="preferred_language">Preferred Language</label>
                                    <div class="form-floating-icon">
                                        <i class="ti tabler-language form-icon"></i>
                                        <select class="form-select @error('preferred_language') is-invalid @enderror" id="preferred_language" name="preferred_language" style="padding-left: 2.5rem;">
                                            @php
                                                $languages = [
                                                    'en' => 'English',
                                                    'es' => 'Spanish',
                                                    'fr' => 'French',
                                                    'de' => 'German',
                                                    'pt' => 'Portuguese',
                                                    'ar' => 'Arabic',
                                                    'zh' => 'Chinese',
                                                    'ja' => 'Japanese',
                                                    'ko' => 'Korean',
                                                    'hi' => 'Hindi',
                                                    'bn' => 'Bengali',
                                                    'ru' => 'Russian',
                                                    'tr' => 'Turkish',
                                                    'it' => 'Italian',
                                                    'nl' => 'Dutch',
                                                ];
                                            @endphp
                                            @foreach($languages as $code => $label)
                                                <option value="{{ $code }}" {{ old('preferred_language', $profile->preferred_language ?: 'en') === $code ? 'selected' : '' }}>
                                                    {{ $label }} ({{ strtoupper($code) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('preferred_language') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="section-icon bg-label-success"><i class="ti tabler-mail-forward"></i></div>
                                <div>
                                    <h5 class="card-title mb-0">Communication</h5>
                                    <small class="text-muted">Manage your email and notification preferences</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="pref-toggle">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="info-icon bg-label-success"><i class="ti tabler-mail-forward"></i></div>
                                    <div>
                                        <h6 class="mb-0">Newsletter Subscription</h6>
                                        <small class="text-muted">Receive promotional emails, product updates, and offers</small>
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="hidden" name="newsletter_subscribed" value="0">
                                    <input type="checkbox" name="newsletter_subscribed" value="1" class="form-check-input"
                                        style="width:3rem;height:1.5rem;"
                                        {{ old('newsletter_subscribed', $profile->newsletter_subscribed) ? 'checked' : '' }}>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti tabler-device-floppy me-1"></i> Save Preferences
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ========== Security Tab ========== --}}
            <div class="tab-pane fade" id="pane-security" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="section-icon bg-label-danger"><i class="ti tabler-lock"></i></div>
                            <div>
                                <h5 class="card-title mb-0">Change Password</h5>
                                <small class="text-muted">Ensure your account uses a strong, unique password</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('profile.password') }}" id="password-form">
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label" for="current_password">Current Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ti tabler-lock"></i></span>
                                        <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                               id="current_password" name="current_password" placeholder="Enter current password" required>
                                        <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="current_password">
                                            <i class="ti tabler-eye"></i>
                                        </button>
                                        @error('current_password', 'updatePassword')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ti tabler-key"></i></span>
                                        <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                               id="password" name="password" placeholder="Enter new password" required>
                                        <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password">
                                            <i class="ti tabler-eye"></i>
                                        </button>
                                        @error('password', 'updatePassword')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ti tabler-key"></i></span>
                                        <input type="password" class="form-control" id="password_confirmation"
                                               name="password_confirmation" placeholder="Confirm new password" required>
                                        <button class="btn btn-outline-secondary toggle-pw" type="button" data-target="password_confirmation">
                                            <i class="ti tabler-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning d-flex align-items-start mt-4" role="alert">
                                <i class="ti tabler-alert-triangle me-2 mt-1"></i>
                                <div>
                                    <strong>Password Requirements:</strong>
                                    <ul class="mb-0 mt-1 ps-3" style="font-size: .8rem">
                                        <li>Minimum 8 characters</li>
                                        <li>At least one uppercase letter and one number</li>
                                        <li>Cannot be the same as your current password</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-danger">
                                    <i class="ti tabler-lock me-1"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('page-js')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Flatpickr for Date of Birth ──
    flatpickr('.flatpickr-dob', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        maxDate: 'today',
        allowInput: false,
        monthSelectorType: 'dropdown',
        disableMobile: true
    });

    // ── Password toggle ──
    document.querySelectorAll('.toggle-pw').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.dataset.target);
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('tabler-eye', 'tabler-eye-off');
            } else {
                input.type = 'password';
                icon.classList.replace('tabler-eye-off', 'tabler-eye');
            }
        });
    });

    // ── Tab from URL hash ──
    var hash = window.location.hash;
    if (hash) {
        var tabBtn = document.querySelector('[data-bs-target="#pane-' + hash.substring(1) + '"]');
        if (tabBtn) new bootstrap.Tab(tabBtn).show();
    }

    var pwErrors = document.querySelectorAll('#pane-security .is-invalid');
    if (pwErrors.length > 0) {
        var secTab = document.getElementById('tab-security');
        if (secTab) new bootstrap.Tab(secTab).show();
    }

    document.querySelectorAll('#profileTabs .nav-link').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function() {
            var id = tab.getAttribute('data-bs-target').replace('#pane-', '');
            history.replaceState(null, '', '#' + id);
        });
    });

    // ── Avatar Upload System ──
    var fileInput    = document.getElementById('avatar_file');
    var dropzone     = document.getElementById('avatar-dropzone');
    var currentWrap  = document.getElementById('avatar-current');
    var placeholder  = document.getElementById('upload-placeholder');
    var previewWrap  = document.getElementById('upload-preview-wrap');
    var previewImg   = document.getElementById('avatar-new-preview');
    var removeAvatar = document.getElementById('remove_avatar');
    var removeBtn    = document.getElementById('btn-remove-avatar');
    var clearBtn     = document.getElementById('btn-clear-upload');

    function showDropzone() {
        if (currentWrap) currentWrap.style.display = 'none';
        dropzone.style.display = '';
        placeholder.classList.remove('d-none');
        previewWrap.classList.add('d-none');
    }

    function showPreview(src) {
        placeholder.classList.add('d-none');
        previewWrap.classList.remove('d-none');
        previewImg.src = src;
        dropzone.style.display = '';
        if (currentWrap) currentWrap.style.display = 'none';
    }

    function handleFile(file) {
        if (!file) return;
        if (!file.type.match(/^image\/(jpeg|png|gif|webp)$/)) {
            alert('Please select a valid image file (JPG, PNG, GIF, or WebP).');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB.');
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) { showPreview(e.target.result); };
        reader.readAsDataURL(file);
        removeAvatar.value = '0';
    }

    // Click on dropzone opens file picker
    if (dropzone) {
        dropzone.addEventListener('click', function(e) {
            if (e.target.closest('.remove-btn')) return;
            fileInput.click();
        });

        // Drag & drop
        ['dragenter','dragover'].forEach(function(evt) {
            dropzone.addEventListener(evt, function(e) { e.preventDefault(); dropzone.classList.add('dragover'); });
        });
        ['dragleave','drop'].forEach(function(evt) {
            dropzone.addEventListener(evt, function(e) { e.preventDefault(); dropzone.classList.remove('dragover'); });
        });
        dropzone.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            if (dt.files.length) {
                fileInput.files = dt.files;
                handleFile(dt.files[0]);
            }
        });
    }

    // File input change
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files.length) handleFile(this.files[0]);
        });
    }

    // Remove existing avatar
    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            removeAvatar.value = '1';
            fileInput.value = '';
            showDropzone();
        });
    }

    // Clear new upload preview
    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            removeAvatar.value = '0';
            placeholder.classList.remove('d-none');
            previewWrap.classList.add('d-none');
            @if($profile->avatar)
                if (currentWrap) {
                    currentWrap.style.display = '';
                    dropzone.style.display = 'none';
                }
            @endif
        });
    }

    // ── Edit Address Modal ──
    document.querySelectorAll('.btn-edit-address').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id   = this.dataset.id;
            var form = document.getElementById('editAddressForm');
            form.action = '{{ url("profile/address") }}/' + id;

            var fields = ['label','first_name','last_name','company','phone',
                          'address_line1','address_line2','city','state',
                          'postal_code','country','type'];

            fields.forEach(function(f) {
                var el = document.getElementById('edit_' + f);
                if (el) el.value = btn.dataset[f] || '';
            });

            document.getElementById('edit_is_default').checked = btn.dataset.is_default === '1';

            new bootstrap.Modal(document.getElementById('editAddressModal')).show();
        });
    });
});
</script>
@endpush
