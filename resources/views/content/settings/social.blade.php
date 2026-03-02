@extends('layouts.app')
@section('title', 'Social Links')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-share"></i></div>
            <div>
                <h4>Social Links</h4>
                <p>Add your social media profile links</p>
            </div>
        </div>

        <form id="settingsForm">
            @csrf
            @method('PUT')

            <div class="card setting-card">
                <div class="card-header">
                    <h5><i class="ti tabler-world text-primary me-2"></i>Social Media Profiles</h5>
                    <p>Links displayed in your website footer and about sections</p>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-facebook"></i></span>
                            <input type="url" name="social[facebook]" class="form-control" value="{{ $settings['facebook'] ?? '' }}" placeholder="https://facebook.com/yourpage">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter / X</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-twitter"></i></span>
                            <input type="url" name="social[twitter]" class="form-control" value="{{ $settings['twitter'] ?? '' }}" placeholder="https://x.com/yourprofile">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-instagram"></i></span>
                            <input type="url" name="social[instagram]" class="form-control" value="{{ $settings['instagram'] ?? '' }}" placeholder="https://instagram.com/yourprofile">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">YouTube</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-youtube"></i></span>
                            <input type="url" name="social[youtube]" class="form-control" value="{{ $settings['youtube'] ?? '' }}" placeholder="https://youtube.com/@yourchannel">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TikTok</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-tiktok"></i></span>
                            <input type="url" name="social[tiktok]" class="form-control" value="{{ $settings['tiktok'] ?? '' }}" placeholder="https://tiktok.com/@yourprofile">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">LinkedIn</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-linkedin"></i></span>
                            <input type="url" name="social[linkedin]" class="form-control" value="{{ $settings['linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Discord</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-discord"></i></span>
                            <input type="url" name="social[discord]" class="form-control" value="{{ $settings['discord'] ?? '' }}" placeholder="https://discord.gg/yourinvite">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telegram</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti tabler-brand-telegram"></i></span>
                            <input type="url" name="social[telegram]" class="form-control" value="{{ $settings['telegram'] ?? '' }}" placeholder="https://t.me/yourchannel">
                        </div>
                    </div>
                </div>
            </div>

            <div class="save-bar">
                <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-js')
<script>
saveSettings('settingsForm', '{{ route("settings.social.update") }}');
</script>
@endpush
