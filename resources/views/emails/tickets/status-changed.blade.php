@extends('emails.tickets.layout')

@section('title', 'Ticket Status Updated')

@php
    $statusConfig = [
        'open'              => ['icon' => '&#128196;', 'bg' => '#dbeafe', 'color' => '#2563eb', 'label' => 'Open'],
        'awaiting_seller'   => ['icon' => '&#128337;', 'bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Awaiting Seller'],
        'awaiting_admin'    => ['icon' => '&#128337;', 'bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Awaiting Admin'],
        'awaiting_customer' => ['icon' => '&#128337;', 'bg' => '#fff7ed', 'color' => '#ea580c', 'label' => 'Awaiting Your Response'],
        'on_hold'           => ['icon' => '&#9208;',  'bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'On Hold'],
        'escalated'         => ['icon' => '&#9888;',  'bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Escalated'],
        'resolved'          => ['icon' => '&#10004;', 'bg' => '#dcfce7', 'color' => '#166534', 'label' => 'Resolved'],
        'closed'            => ['icon' => '&#128274;','bg' => '#f1f5f9', 'color' => '#475569', 'label' => 'Closed'],
    ];
    $cfg = $statusConfig[$newStatus] ?? ['icon' => '&#128196;', 'bg' => '#dbeafe', 'color' => '#2563eb', 'label' => ucwords(str_replace('_', ' ', $newStatus))];
@endphp

@section('banner')
<div class="status-icon" style="background:{{ $cfg['bg'] }}; color:{{ $cfg['color'] }}">{!! $cfg['icon'] !!}</div>
<h2 class="status-title">{{ $cfg['label'] }}</h2>
<p class="status-sub">Your ticket status has been updated</p>
@endsection

@section('content')
<p class="greeting">Hello <strong>{{ $recipientName }}</strong>,</p>
<p>Your support ticket <strong>{{ $ticket->ticket_number }}</strong> has been updated to a new status.</p>

@include('emails.tickets._ticket-meta')

<div class="meta-card" style="background:{{ $cfg['bg'] }}20; border:1px solid {{ $cfg['bg'] }};">
    <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="font-size:13px;color:#718096;font-weight:600;padding:4px 0;">Previous Status</td>
            <td style="text-align:right;font-weight:600;padding:4px 0;">
                <span class="badge badge-secondary">{{ ucwords(str_replace('_', ' ', $oldStatus ?? $ticket->status)) }}</span>
            </td>
        </tr>
        <tr>
            <td style="font-size:13px;color:#718096;font-weight:600;padding:4px 0;">New Status</td>
            <td style="text-align:right;font-weight:600;padding:4px 0;">
                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
            </td>
        </tr>
    </table>
</div>

@if($newStatus === 'resolved')
<div class="info-box">
    Your issue has been marked as <strong>resolved</strong>. If you still need help, you can reopen this ticket within the next 7 days by replying.
</div>
@elseif($newStatus === 'closed')
<div class="info-box">
    This ticket has been <strong>closed</strong>. If you need further assistance, please create a new support ticket and reference <strong>{{ $ticket->ticket_number }}</strong>.
</div>
@elseif($newStatus === 'awaiting_customer')
<div class="info-box">
    Our team is awaiting your response. Please reply at your earliest convenience so we can continue helping you.
</div>
@elseif($newStatus === 'on_hold')
<div class="info-box">
    Your ticket has been placed on hold. We'll get back to you as soon as we have an update.
</div>
@endif

<div class="cta-wrapper">
    <a href="{{ $viewUrl }}" class="cta-btn">View Ticket</a>
</div>
@endsection
