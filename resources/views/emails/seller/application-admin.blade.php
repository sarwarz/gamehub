@extends('emails.seller.layout')

@section('title', 'New Seller Application')

@section('banner')
<div class="status-icon" style="background:#fef9c3; color:#854d0e">&#128203;</div>
<h2 class="status-title">New Seller Application</h2>
<p class="status-sub">Requires your review and approval</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>A new seller application has been submitted and is awaiting your review.</p>

<p class="section-title">Applicant Details</p>
<div class="meta-card">
    <table cellpadding="0" cellspacing="0" width="100%">
        @if($seller->user)
        <tr>
            <td class="meta-label" style="padding:6px 0;">User</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->user->name }} ({{ $seller->user->email }})</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label" style="padding:6px 0;">Store Name</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->store_name }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Store Email</td>
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
        @if($seller->registration_number)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Reg. Number</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->registration_number }}</td>
        </tr>
        @endif
        @if($seller->vat_number)
        <tr>
            <td class="meta-label" style="padding:6px 0;">VAT Number</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->vat_number }}</td>
        </tr>
        @endif
        @if($seller->website)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Website</td>
            <td class="meta-value" style="padding:6px 0;"><a href="{{ $seller->website }}" style="color:#7367f0;">{{ $seller->website }}</a></td>
        </tr>
        @endif
        <tr>
            <td class="meta-label" style="padding:6px 0;">Applied</td>
            <td class="meta-value" style="padding:6px 0;">{{ $seller->created_at->format('M d, Y h:i A') }}</td>
        </tr>
    </table>
</div>

@if($seller->description)
<p class="section-title">Store Description</p>
<div class="message-bubble">
    {!! nl2br(e($seller->description)) !!}
</div>
@endif

<hr class="divider">

<div class="info-box" style="background:#fef9c320; border:1px solid #fde68a;">
    <strong>&#9888; Action Required:</strong> Please review this application and approve or reject it from the admin panel.
</div>

<div class="cta-wrapper">
    <a href="{{ url('/dashboard/sellers') }}" class="cta-btn">Review in Dashboard</a>
</div>
@endsection
