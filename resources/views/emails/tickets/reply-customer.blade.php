@extends('emails.tickets.layout')

@section('title', 'New Reply on Your Ticket')

@section('banner')
<div class="status-icon" style="background:#ede9fe; color:#7367f0">&#128172;</div>
<h2 class="status-title">New Reply on Your Ticket</h2>
<p class="status-sub">{{ $ticket->ticket_number }}</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>There is a new reply on your support ticket <strong>{{ $ticket->ticket_number }}</strong>.</p>

@include('emails.tickets._ticket-meta')

<p class="section-title">Reply from {{ $senderName }}</p>
<div class="message-bubble">
    {!! nl2br(e(Str::limit($messageBody, 500))) !!}
</div>

@if($hasAttachments)
<div class="info-box">
    &#128206; This reply includes attachments. View them in the ticket portal.
</div>
@endif

<hr class="divider">

<div class="info-box">
    If you have further questions, you can reply directly from the ticket page. Our team is committed to resolving your issue as quickly as possible.
</div>

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View Ticket</a>
</div>
@endsection
