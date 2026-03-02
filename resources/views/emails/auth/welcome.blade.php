@extends('emails.auth.layout')

@section('title', 'Welcome to ' . config('app.name'))

@section('banner')
<div class="status-icon" style="background:#dcfce7; color:#16a34a">&#127881;</div>
<h2 class="status-title">Welcome Aboard!</h2>
<p class="status-sub">Your account has been created successfully</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>Welcome to <strong>{{ config('app.name') }}</strong>! We're excited to have you join our community. Your account is now ready and here's what you can do:</p>

<div style="margin:24px 0;">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding:12px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:48px; vertical-align:top;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#ede9fe; color:#7367f0; text-align:center; line-height:40px; font-size:18px;">&#128270;</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:700; font-size:14px; color:#2d3748; margin-bottom:2px;">Browse Products</div>
                            <div style="font-size:13px; color:#718096;">Discover thousands of games, software, and digital products at great prices.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:48px; vertical-align:top;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#dbeafe; color:#2563eb; text-align:center; line-height:40px; font-size:18px;">&#128176;</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:700; font-size:14px; color:#2d3748; margin-bottom:2px;">Secure Payments</div>
                            <div style="font-size:13px; color:#718096;">Pay safely with multiple payment methods and get instant delivery of digital products.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:48px; vertical-align:top;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#dcfce7; color:#16a34a; text-align:center; line-height:40px; font-size:18px;">&#127775;</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:700; font-size:14px; color:#2d3748; margin-bottom:2px;">Become a Seller</div>
                            <div style="font-size:13px; color:#718096;">Want to sell your own keys? Open a seller store and start earning today.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:48px; vertical-align:top;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#fee2e2; color:#dc2626; text-align:center; line-height:40px; font-size:18px;">&#128172;</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:700; font-size:14px; color:#2d3748; margin-bottom:2px;">24/7 Support</div>
                            <div style="font-size:13px; color:#718096;">Need help? Our support team is always ready to assist you via tickets.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<hr class="divider">

<div class="info-box">
    <strong>Next Step:</strong> Check your inbox for a verification email and verify your email address to unlock all features.
</div>

<div class="cta-wrapper">
    <a href="{{ $shopUrl }}" class="cta-btn">Start Shopping</a>
</div>
@endsection
