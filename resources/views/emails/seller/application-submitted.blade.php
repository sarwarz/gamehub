@extends('emails.seller.layout')

@section('title', 'Seller Application Received')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#128203;</div>
<h2 class="status-title">Application Received!</h2>
<p class="status-sub">We're reviewing your seller application</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>Thank you for applying to become a seller on <strong>{{ config('app.name') }}</strong>! We've received your application and our team will review it shortly.</p>

<p class="section-title">Application Summary</p>
<div class="meta-card">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="meta-label" style="padding:6px 0;">Store Name</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->store_name }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Store URL</td>
            <td class="meta-value" style="padding:6px 0;">{{ url('/store/' . $seller->slug) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Contact Email</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->email }}</td>
        </tr>
        @if($seller->phone)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Phone</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->phone }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label" style="padding:6px 0;">Location</td>
            <td class="meta-value" style="padding:6px 0;">{{ collect([$seller->city, $seller->state, $seller->country])->filter()->implode(', ') }}</td>
        </tr>
        @if($seller->company_name)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Company</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->company_name }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label" style="padding:6px 0;">Status</td>
            <td class="meta-value" style="padding:6px 0;">
                <span class="badge badge-warning">Pending Review</span>
            </td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Submitted</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->created_at->format('M d, Y h:i A') }}</td>
        </tr>
    </table>
</div>

<hr class="divider">

<p class="section-title">What Happens Next?</p>
<div style="margin:16px 0;">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding:10px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:40px; vertical-align:top;">
                            <div style="width:32px; height:32px; border-radius:50%; background:#dbeafe; color:#2563eb; text-align:center; line-height:32px; font-weight:700; font-size:14px;">1</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:600; font-size:14px; color:#2d3748;">Review</div>
                            <div style="font-size:13px; color:#718096;">Our team will review your application within 1-3 business days.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:40px; vertical-align:top;">
                            <div style="width:32px; height:32px; border-radius:50%; background:#ede9fe; color:#7367f0; text-align:center; line-height:32px; font-weight:700; font-size:14px;">2</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:600; font-size:14px; color:#2d3748;">Approval</div>
                            <div style="font-size:13px; color:#718096;">Once approved, you'll receive an email with instructions to set up your store.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:10px 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="width:40px; vertical-align:top;">
                            <div style="width:32px; height:32px; border-radius:50%; background:#dcfce7; color:#16a34a; text-align:center; line-height:32px; font-weight:700; font-size:14px;">3</div>
                        </td>
                        <td style="padding-left:12px; vertical-align:top;">
                            <div style="font-weight:600; font-size:14px; color:#2d3748;">Start Selling</div>
                            <div style="font-size:13px; color:#718096;">List your products, set competitive prices, and start earning!</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="info-box">
    You can update your application details anytime while it's still under review. If you have questions, feel free to contact our support team.
</div>
@endsection
