@extends('emails.auth.layout')

@section('title', 'Reset Your Password')

@section('banner')
<div class="status-icon" style="background:#fef9c3; color:#854d0e">&#128274;</div>
<h2 class="status-title">Reset Your Password</h2>
<p class="status-sub">We received a password reset request</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>We received a request to reset the password for your <strong>{{ config('app.name') }}</strong> account. Click the button below to choose a new password.</p>

<div class="cta-wrapper" style="margin:32px 0;">
    <a href="{{ $resetUrl }}" class="cta-btn" style="background:linear-gradient(135deg, #ea580c 0%, #f97316 100%); box-shadow:0 4px 14px rgba(234,88,12,0.35);">Reset Password</a>
</div>

<div class="meta-card">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="meta-label" style="padding:6px 0;">Link Expires In</td>
            <td class="meta-value" style="padding:6px 0;">{{ $expireMinutes }} minutes</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Requested At</td>
            <td class="meta-value" style="padding:6px 0;">{{ now()->format('M d, Y h:i A') }}</td>
        </tr>
    </table>
</div>

<hr class="divider">

<div class="info-box" style="background:#fff7ed; border:1px solid #fed7aa;">
    <strong>&#9888; Security Notice:</strong> If you did not request a password reset, please ignore this email. Your password will remain unchanged, and this link will expire automatically after {{ $expireMinutes }} minutes.
</div>

<p style="font-size:12px; color:#a0aec0; margin-top:20px; word-break:break-all;">
    If the button above doesn't work, copy and paste this URL into your browser:<br>
    <a href="{{ $resetUrl }}" style="color:#7367f0;">{{ $resetUrl }}</a>
</p>
@endsection
