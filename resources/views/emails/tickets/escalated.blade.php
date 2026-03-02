@extends('emails.tickets.layout')

@section('title', 'Ticket Escalated')

@section('banner')
<div class="status-icon" style="background:#fee2e2; color:#dc2626">&#9888;</div>
<h2 class="status-title">Ticket Escalated</h2>
<p class="status-sub">Requires immediate admin attention</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>A support ticket has been <strong>escalated</strong> and requires immediate admin intervention. This ticket was previously handled by a seller and now needs higher-level support.</p>

@include('emails.tickets._ticket-meta', ['showCustomer' => true])

@if($ticket->seller)
<p class="section-title">Escalation Details</p>
<div class="meta-card" style="background:#fee2e220; border:1px solid #fecaca;">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="meta-label" style="padding:6px 0;">Previous Handler</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->seller->store_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Escalated At</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->escalated_at?->format('M d, Y h:i A') ?? now()->format('M d, Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Total Messages</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->messages->count() }}</td>
        </tr>
    </table>
</div>
@endif

@if($ticket->messages->count())
@php $lastMsg = $ticket->messages->last(); @endphp
<p class="section-title">Latest Message</p>
<div class="message-bubble">
    <div style="font-size:12px;color:#a0aec0;margin-bottom:8px;">
        <strong>{{ $lastMsg->user->name ?? ucfirst($lastMsg->sender_role) }}</strong>
        &bull; {{ $lastMsg->created_at->format('M d, Y h:i A') }}
    </div>
    {!! nl2br(e(Str::limit($lastMsg->message, 400))) !!}
</div>
@endif

@if($ticket->order_id && $ticket->order)
<p class="section-title">Related Order</p>
<div class="meta-card">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td class="meta-label" style="padding:4px 0;">Order #</td>
            <td class="meta-value" style="padding:4px 0;">{{ $ticket->order->id }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:4px 0;">Amount</td>
            <td class="meta-value" style="padding:4px 0;">{{ format_currency($ticket->order->total) }}</td>
        </tr>
    </table>
</div>
@endif

<hr class="divider">

<div class="info-box" style="background:#fee2e220; border:1px solid #fecaca;">
    <strong>&#9888; Action Required:</strong> Please review the full conversation history, contact the customer, and work toward a resolution. Escalated tickets should be prioritized.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn" style="background:linear-gradient(135deg, #dc2626 0%, #ef4444 100%); box-shadow:0 4px 14px rgba(220,38,38,0.35);">View & Resolve</a>
</div>
@endsection
