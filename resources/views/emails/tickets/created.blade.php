@extends('emails.tickets.layout')

@section('title', 'New Support Ticket')

@section('banner')
<div class="status-icon" style="background:#dbeafe; color:#2563eb">&#128172;</div>
<h2 class="status-title">New Ticket Created</h2>
<p class="status-sub">A new support ticket requires your attention</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>A new support ticket has been submitted and needs your review. Please respond as soon as possible to ensure timely resolution.</p>

@include('emails.tickets._ticket-meta', ['showCustomer' => true])

@if($ticket->messages->count())
@php $firstMsg = $ticket->messages->first(); @endphp
<p class="section-title">Initial Message</p>
<div class="message-bubble">
    {!! nl2br(e(Str::limit($firstMsg->message, 400))) !!}
</div>
@endif

@if($ticket->seller)
<div class="info-box">
    <strong>Assigned Seller:</strong> {{ $ticket->seller->store_name ?? 'N/A' }}
</div>
@endif

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View & Respond</a>
</div>
@endsection
