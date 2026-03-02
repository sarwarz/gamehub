@extends('emails.auth.layout')

@section('title', 'Verify Your Email')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#9993;</div>
<h2 class="status-title">Verify Your Email</h2>
<p class="status-sub">One quick step to get started</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>Thank you for creating an account with <strong>{{ config('app.name') }}</strong>! Please verify your email address by clicking the button below to activate your account and start exploring.</p>

<div class="cta-wrapper" style="margin:32px 0;">
    <a href="{{ $verificationUrl }}" class="cta-btn">Verify Email Address</a>
</div>

<div class="meta-card">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="meta-label" style="padding:6px 0;">Link Expires In</td>
            <td class="meta-value" style="padding:6px 0;">{{ $expireMinutes }} minutes</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Email</td>
            <td class="meta-value" style="padding:6px 0;">{{ $recipientName }}</td>
        </tr>
    </table>
</div>

<hr class="divider">

<div class="info-box">
    If you did not create an account, no further action is required. This link will expire automatically.
</div>

<p style="font-size:12px; color:#a0aec0; margin-top:20px; word-break:break-all;">
    If the button above doesn't work, copy and paste this URL into your browser:<br>
    <a href="{{ $verificationUrl }}" style="color:#7367f0;">{{ $verificationUrl }}</a>
</p>
@endsection
