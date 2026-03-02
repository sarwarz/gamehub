@extends('emails.tickets.layout')

@section('title', 'Ticket Assigned to You')

@section('banner')
<div class="status-icon" style="background:#ede9fe; color:#7367f0">&#128100;</div>
<h2 class="status-title">Ticket Assigned</h2>
<p class="status-sub">A support ticket has been assigned to you</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>A support ticket has been assigned to you and requires your attention. Please review the details below and respond to the customer as soon as possible.</p>

@include('emails.tickets._ticket-meta', ['showCustomer' => true])

@if($ticket->messages->count())
@php $firstMsg = $ticket->messages->first(); @endphp
<p class="section-title">Initial Message</p>
<div class="message-bubble">
    {!! nl2br(e(Str::limit($firstMsg->message, 400))) !!}
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
        <tr>
            <td class="meta-label" style="padding:4px 0;">Order Status</td>
            <td class="meta-value" style="padding:4px 0;">
                <span class="badge badge-info">{{ ucfirst($ticket->order->status) }}</span>
            </td>
        </tr>
    </table>
</div>
@endif

<hr class="divider">

<div class="info-box">
    <strong>Tip:</strong> Respond promptly and professionally. Check the full conversation history in the admin panel for context.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View & Respond</a>
</div>
@endsection
