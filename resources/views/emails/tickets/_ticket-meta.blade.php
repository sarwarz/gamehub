@php
    $priorityColors = [
        'low'    => '#64748b',
        'medium' => '#2563eb',
        'high'   => '#ea580c',
        'urgent' => '#dc2626',
    ];
    $priorityBg = [
        'low'    => '#f1f5f9',
        'medium' => '#dbeafe',
        'high'   => '#fff7ed',
        'urgent' => '#fee2e2',
    ];
    $pc = $priorityColors[$ticket->priority] ?? '#64748b';
    $pb = $priorityBg[$ticket->priority] ?? '#f1f5f9';
@endphp

<div class="meta-card">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label" style="padding:6px 0;">Ticket Number</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->ticket_number }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Subject</td>
            <td class="meta-value" style="padding:6px 0;">{{ Str::limit($ticket->subject, 50) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Department</td>
            <td class="meta-value" style="padding:6px 0;">{{ ucfirst($ticket->department) }}</td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Priority</td>
            <td class="meta-value" style="padding:6px 0;">
                <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;background:{{ $pb }};color:{{ $pc }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Status</td>
            <td class="meta-value" style="padding:6px 0;">
                <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
            </td>
        </tr>
        <tr>
            <td class="meta-label" style="padding:6px 0;">Created</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->created_at->format('M d, Y h:i A') }}</td>
        </tr>
        @if(isset($showCustomer) && $showCustomer && $ticket->user)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Customer</td>
            <td class="meta-value" style="padding:6px 0;">{{ $ticket->user->name }} ({{ $ticket->user->email }})</td>
        </tr>
        @endif
        @if($ticket->order_id)
        <tr>
            <td class="meta-label" style="padding:6px 0;">Related Order</td>
            <td class="meta-value" style="padding:6px 0;">#{{ $ticket->order_id }}</td>
        </tr>
        @endif
    </table>
</div>
