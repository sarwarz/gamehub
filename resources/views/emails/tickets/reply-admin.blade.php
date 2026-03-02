@extends('emails.tickets.layout')

@section('title', 'Customer Reply on Ticket')

@section('banner')
<div class="status-icon" style="background:#fef9c3; color:#854d0e">&#128172;</div>
<h2 class="status-title">Customer Reply</h2>
<p class="status-sub">{{ $ticket->ticket_number }} needs your attention</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>A customer has replied on support ticket <strong>{{ $ticket->ticket_number }}</strong>. Please review and respond promptly.</p>

@include('emails.tickets._ticket-meta', ['showCustomer' => true])

<p class="section-title">Customer's Reply</p>
<div class="message-bubble">
    {!! nl2br(e(Str::limit($messageBody, 500))) !!}
</div>

@if($hasAttachments)
<div class="info-box">
    &#128206; This reply includes attachments. View them in the admin panel.
</div>
@endif

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View & Respond</a>
</div>
@endsection
