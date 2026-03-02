@extends('layouts.app')

@section('title', $supportTicket->ticket_number . ' - Support Ticket')

@php
    $statusColors = [
        'open' => 'primary', 'awaiting_seller' => 'warning', 'awaiting_admin' => 'info',
        'awaiting_customer' => 'dark', 'on_hold' => 'secondary', 'escalated' => 'danger',
        'resolved' => 'success', 'closed' => 'secondary',
    ];
    $statusIcons = [
        'open' => 'tabler-circle-dot', 'awaiting_seller' => 'tabler-clock', 'awaiting_admin' => 'tabler-clock',
        'awaiting_customer' => 'tabler-user-pause', 'on_hold' => 'tabler-player-pause', 'escalated' => 'tabler-alert-triangle',
        'resolved' => 'tabler-circle-check', 'closed' => 'tabler-lock',
    ];
    $pColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
    $pIcons  = ['low' => 'tabler-arrow-down', 'medium' => 'tabler-minus', 'high' => 'tabler-arrow-up', 'urgent' => 'tabler-flame'];
    $dColors = ['order' => 'primary', 'payment' => 'warning', 'account' => 'info', 'product' => 'success', 'general' => 'secondary'];
    $customer = $supportTicket->user;

    $frt = $sla['first_reply_mins'];
    $frtLabel = $frt !== null ? ($frt < 60 ? round($frt) . 'm' : round($frt / 60, 1) . 'h') : null;
    $resMin = $sla['resolution_mins'];
    $resLabel = $resMin !== null ? ($resMin < 60 ? round($resMin) . 'm' : ($resMin < 1440 ? round($resMin / 60, 1) . 'h' : round($resMin / 1440, 1) . 'd')) : null;
    $aiEnabled = \App\Services\AiContentService::isEnabled();
    $order = $supportTicket->order;
@endphp

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-support-tickets.css') }}">
<style>
    /* AI Reply button */
    .btn-ai-reply {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: #fff;
        border: none;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: .72rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-ai-reply:hover { opacity: .9; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(124,58,237,.3); color: #fff; }
    .btn-ai-reply:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }
    .btn-ai-reply .spinner-border { width: 12px; height: 12px; border-width: 2px; }
    .ai-instruction-row {
        display: none;
        padding: 8px 1.25rem;
        background: linear-gradient(135deg, rgba(124,58,237,.04) 0%, rgba(168,85,247,.04) 100%);
        border-top: 1px solid rgba(124,58,237,.12);
        align-items: center;
        gap: 8px;
    }
    .ai-instruction-row.show { display: flex; }
    .ai-instruction-row input {
        flex: 1;
        border: 1px solid rgba(124,58,237,.2);
        border-radius: 6px;
        padding: 5px 10px;
        font-size: .78rem;
        outline: none;
        background: #fff;
    }
    .ai-instruction-row input:focus { border-color: #7c3aed; box-shadow: 0 0 0 2px rgba(124,58,237,.1); }
    .ai-instruction-row input::placeholder { color: #b0aab8; }
    .btn-ai-generate {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: #fff;
        border: none;
        padding: 5px 14px;
        border-radius: 6px;
        font-size: .72rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    .btn-ai-generate:hover { opacity: .9; }
    .btn-ai-generate:disabled { opacity: .6; cursor: not-allowed; }
    .btn-ai-cancel { background: none; border: none; color: #a1acb8; padding: 4px; cursor: pointer; font-size: .85rem; }
    .btn-ai-cancel:hover { color: #ea5455; }
</style>
@endpush

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <i class="ti tabler-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════ PREMIUM HEADER ═══════ --}}
<div class="tkt-header">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2" style="position:relative;z-index:1">
        <div>
            <a href="{{ route('support-tickets.index') }}" class="tkt-back"><i class="ti tabler-arrow-left me-1"></i>Back to Tickets</a>
            <h4 class="tkt-title">
                {{ $supportTicket->ticket_number }}
                <span class="badge bg-{{ $statusColors[$supportTicket->status] ?? 'secondary' }}" style="font-size:.68rem">
                    <i class="ti {{ $statusIcons[$supportTicket->status] ?? 'tabler-circle' }} me-1" style="font-size:.7rem"></i>{{ ucwords(str_replace('_', ' ', $supportTicket->status)) }}
                </span>
                @if($supportTicket->is_escalated)
                    <span class="badge bg-danger" style="font-size:.62rem"><i class="ti tabler-alert-triangle me-1"></i>Escalated</span>
                @endif
                <span class="badge bg-label-{{ $pColors[$supportTicket->priority] ?? 'secondary' }}" style="font-size:.62rem;background:rgba(255,255,255,.15)!important;color:rgba(255,255,255,.85)">
                    <i class="ti {{ $pIcons[$supportTicket->priority] ?? 'tabler-minus' }} me-1" style="font-size:.65rem"></i>{{ ucfirst($supportTicket->priority) }}
                </span>
            </h4>
            <p class="tkt-subject">{{ $supportTicket->subject }}</p>
        </div>
        <div class="tkt-actions d-flex gap-2 mt-1">
            @if($supportTicket->isOpen())
                <button class="btn btn-success btn-sm btn-resolve"><i class="ti tabler-circle-check me-1"></i>Resolve</button>
                @if(!$supportTicket->is_escalated && $supportTicket->seller_id)
                <button class="btn btn-danger btn-sm btn-escalate"><i class="ti tabler-arrow-up me-1"></i>Escalate</button>
                @endif
            @endif
            @if($supportTicket->status === 'resolved')
                <button class="btn btn-outline-light btn-sm btn-close-ticket"><i class="ti tabler-lock me-1"></i>Close</button>
            @endif
        </div>
    </div>
</div>

{{-- ═══════ SLA METRICS ═══════ --}}
<div class="tkt-sla">
    <div class="tkt-sla-card">
        <div class="tkt-sla-icon bg-label-primary"><i class="ti tabler-clock"></i></div>
        <div class="tkt-sla-val text-primary">{{ $sla['created_ago'] }}</div>
        <div class="tkt-sla-lbl">Created</div>
    </div>
    <div class="tkt-sla-card">
        <div class="tkt-sla-icon bg-label-{{ $frt !== null ? (($frt > 60) ? 'danger' : 'success') : 'warning' }}">
            <i class="ti tabler-message-reply"></i>
        </div>
        <div class="tkt-sla-val {{ $frt !== null ? (($frt > 60) ? 'text-danger' : 'text-success') : 'text-warning' }}">{{ $frtLabel ?? 'Pending' }}</div>
        <div class="tkt-sla-lbl">First Response</div>
    </div>
    <div class="tkt-sla-card">
        <div class="tkt-sla-icon bg-label-{{ $resMin !== null ? (($resMin > 1440) ? 'warning' : 'success') : 'secondary' }}">
            <i class="ti tabler-circle-check"></i>
        </div>
        <div class="tkt-sla-val {{ $resMin !== null ? (($resMin > 1440) ? 'text-warning' : 'text-success') : '' }}">{{ $resLabel ?? '—' }}</div>
        <div class="tkt-sla-lbl">Resolution</div>
    </div>
    <div class="tkt-sla-card">
        <div class="tkt-sla-icon bg-label-info"><i class="ti tabler-messages"></i></div>
        <div class="tkt-sla-val">{{ $supportTicket->messages->count() }}</div>
        <div class="tkt-sla-lbl">Messages</div>
    </div>
</div>

{{-- ═══════ MAIN LAYOUT ═══════ --}}
<div class="row g-4">
    {{-- LEFT: Conversation --}}
    <div class="col-lg-9">
        {{-- Quick canned bar --}}
        @if($supportTicket->status !== 'closed' && $cannedResponses->count())
        <div class="tkt-canned-bar">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted" style="font-size:.7rem"><i class="ti tabler-bolt me-1"></i>Quick:</span>
                @foreach($cannedResponses->take(5) as $cr)
                <button type="button" class="btn btn-xs btn-outline-primary canned-quick-btn" data-body="{{ $cr->body }}" data-bs-toggle="tooltip" title="{{ $cr->title }}" style="font-size:.68rem;border-radius:4px">{{ $cr->shortcut }}</button>
                @endforeach
                <button type="button" class="btn btn-xs btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#cannedOffcanvas" style="font-size:.68rem;border-radius:4px"><i class="ti tabler-template me-1"></i>All</button>
            </div>
        </div>
        @endif

        {{-- Chat Card --}}
        <div class="card ticket-chat-card">
            <div class="ticket-chat-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center overflow-hidden">
                    <div class="avatar avatar-sm me-3">
                        @if($customer && $customer->avatar)
                            <img src="{{ asset($customer->avatar) }}" class="rounded-circle" alt="">
                        @else
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($customer->name ?? 'C', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <h6 class="mb-0 fw-semibold" style="font-size:.85rem">{{ $customer->name ?? 'Customer' }}</h6>
                        <small class="text-body-secondary" style="font-size:.72rem">{{ $customer->email ?? '' }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-{{ $dColors[$supportTicket->department] ?? 'secondary' }}" style="font-size:.62rem">
                        <i class="ti tabler-tag me-1" style="font-size:.62rem"></i>{{ ucfirst($supportTicket->department) }}
                    </span>
                    @if($order)
                    <a href="{{ route('orders.show', $order->id) }}" class="badge bg-label-primary text-decoration-none" style="font-size:.62rem">
                        <i class="ti tabler-shopping-cart me-1" style="font-size:.62rem"></i>#{{ $order->order_number }}
                    </a>
                    @endif
                </div>
            </div>

            <div class="ticket-chat-body" id="chat-thread">
                <ul class="list-unstyled chat-history mb-0">
                    @forelse($supportTicket->messages as $msg)
                        @if($msg->is_internal_note)
                            <li class="chat-message chat-message-note">
                                <div class="chat-message-wrapper">
                                    <div class="chat-message-text">
                                        <div class="msg-sender"><i class="ti tabler-note" style="font-size:.82rem"></i> Internal Note <span class="role-pill role-pill-admin">{{ $msg->user->name ?? 'Staff' }}</span></div>
                                        <p>{{ $msg->message }}</p>
                                    </div>
                                    <div class="chat-msg-time">{{ $msg->created_at->format('M d, Y H:i') }}</div>
                                </div>
                            </li>
                        @elseif($msg->sender_role === 'customer')
                            <li class="chat-message">
                                <div class="d-flex overflow-hidden">
                                    <div class="user-avatar flex-shrink-0 me-3">
                                        <div class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($msg->user->name ?? 'C', 0, 1)) }}</span></div>
                                    </div>
                                    <div class="chat-message-wrapper flex-grow-1">
                                        <div class="chat-message-text">
                                            <div class="msg-sender">{{ $msg->user->name ?? 'Customer' }} <span class="role-pill role-pill-customer">Customer</span></div>
                                            <p>{{ $msg->message }}</p>
                                            @if($msg->attachments)
                                            <div class="bubble-attachments">
                                                @foreach($msg->attachments as $att)<a href="{{ asset($att) }}" target="_blank"><i class="ti tabler-paperclip"></i> {{ basename($att) }}</a>@endforeach
                                            </div>
                                            @endif
                                        </div>
                                        <div class="chat-msg-time">{{ $msg->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                        @else
                            <li class="chat-message chat-message-right">
                                <div class="d-flex overflow-hidden w-100 justify-content-end">
                                    <div class="chat-message-wrapper">
                                        <div class="chat-message-text">
                                            <div class="msg-sender">{{ $msg->user->name ?? ucfirst($msg->sender_role) }} <span class="role-pill role-pill-{{ $msg->sender_role }}">{{ ucfirst($msg->sender_role) }}</span></div>
                                            <p>{{ $msg->message }}</p>
                                            @if($msg->attachments)
                                            <div class="bubble-attachments">
                                                @foreach($msg->attachments as $att)<a href="{{ asset($att) }}" target="_blank"><i class="ti tabler-paperclip"></i> {{ basename($att) }}</a>@endforeach
                                            </div>
                                            @endif
                                        </div>
                                        <div class="chat-msg-time text-end"><i class="ti tabler-checks text-success me-1" style="font-size:.72rem"></i>{{ $msg->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                    <div class="user-avatar flex-shrink-0 ms-3">
                                        <div class="avatar avatar-sm"><span class="avatar-initial rounded-circle bg-label-{{ $msg->sender_role === 'admin' ? 'danger' : 'success' }}">{{ strtoupper(substr($msg->user->name ?? 'S', 0, 1)) }}</span></div>
                                    </div>
                                </div>
                            </li>
                        @endif
                    @empty
                        <li class="text-center text-muted py-5">
                            <div class="bg-label-primary p-4 rounded-circle d-inline-flex mb-3"><i class="ti tabler-messages-off" style="font-size:2rem"></i></div>
                            <p class="mb-0">No messages yet.</p>
                        </li>
                    @endforelse
                </ul>
            </div>

            @if($supportTicket->status !== 'closed')
            <form id="reply-form" method="POST" action="{{ route('support-tickets.reply', $supportTicket->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="ticket-chat-footer">
                    <textarea name="message" id="reply-message" class="form-control message-input" rows="3" placeholder="Type your reply… (Ctrl+V to paste images, type /shortcut for templates)" required></textarea>
                    <div class="attachment-previews" id="attachment-previews"></div>
                    @if($aiEnabled)
                    <div class="ai-instruction-row" id="ai-instruction-row">
                        <i class="ti tabler-sparkles" style="color:#7c3aed;font-size:.9rem"></i>
                        <input type="text" id="ai-instruction" placeholder="Optional: tell AI what to focus on, e.g. 'offer a refund' or 'ask for screenshot'…">
                        <button type="button" class="btn-ai-generate" id="btn-ai-generate"><i class="ti tabler-sparkles me-1"></i>Generate</button>
                        <button type="button" class="btn-ai-cancel" id="btn-ai-cancel" title="Cancel"><i class="ti tabler-x"></i></button>
                    </div>
                    @endif
                    <div class="chat-footer-actions">
                        <div class="d-flex align-items-center gap-2">
                            <label class="btn btn-text-secondary btn-icon rounded-pill mb-0" style="cursor:pointer" data-bs-toggle="tooltip" title="Attach files">
                                <i class="ti tabler-paperclip" style="font-size:1.15rem"></i>
                                <input type="file" id="file-input" name="attachments[]" multiple hidden accept="image/*,.pdf,.doc,.docx,.zip">
                            </label>
                            <span class="paste-hint d-none d-lg-inline" style="font-size:.68rem;color:#a1acb8"><i class="ti tabler-clipboard me-1"></i>Ctrl+V</span>
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" name="is_internal_note" value="1" id="chk-note">
                                <label class="form-check-label" for="chk-note" style="font-size:.75rem">Internal Note</label>
                            </div>
                            @if($aiEnabled)
                            <button type="button" class="btn-ai-reply" id="btn-ai-reply" data-bs-toggle="tooltip" title="Draft reply with AI">
                                <i class="ti tabler-sparkles" style="font-size:.8rem"></i> AI Reply
                            </button>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1" id="btn-send-reply" style="border-radius:6px">
                            <span class="d-none d-sm-inline-block">Send</span><i class="ti tabler-send" style="font-size:.95rem"></i>
                        </button>
                    </div>
                </div>
            </form>
            @else
            <div class="ticket-chat-closed"><i class="ti tabler-lock me-2"></i>Ticket closed — no further replies.</div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="col-lg-3">

        {{-- ── Actions Card ── --}}
        <div class="card sb-card mb-3">
            <div class="card-header">
                <h6><i class="ti tabler-adjustments-horizontal me-1 text-primary"></i>Actions</h6>
            </div>
            <div class="card-body sb-actions" style="padding:.875rem !important">
                <label class="form-label">Assign</label>
                <div class="d-flex gap-1">
                    <select class="form-select form-select-sm" id="assign-admin" style="flex:1">
                        <option value="">Unassigned</option>
                        @foreach($admins as $admin)<option value="{{ $admin->id }}" {{ $supportTicket->assigned_admin_id == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>@endforeach
                    </select>
                    <button class="btn btn-sm btn-icon btn-primary" id="btn-assign" data-bs-toggle="tooltip" title="Save"><i class="ti tabler-check" style="font-size:.8rem"></i></button>
                </div>
                <hr>
                <label class="form-label">Status</label>
                <div class="d-flex gap-1">
                    <select class="form-select form-select-sm" id="change-status" style="flex:1">
                        @foreach(\App\Models\SupportTicket::STATUSES as $s)<option value="{{ $s }}" {{ $supportTicket->status === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
                    </select>
                    <button class="btn btn-sm btn-icon btn-primary" id="btn-change-status" data-bs-toggle="tooltip" title="Update"><i class="ti tabler-check" style="font-size:.8rem"></i></button>
                </div>
                <hr>
                <label class="form-label">Priority</label>
                <div class="d-flex gap-1">
                    <select class="form-select form-select-sm" id="change-priority" style="flex:1">
                        @foreach(\App\Models\SupportTicket::PRIORITIES as $p)<option value="{{ $p }}" {{ $supportTicket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                    <button class="btn btn-sm btn-icon btn-warning" id="btn-change-priority" data-bs-toggle="tooltip" title="Update"><i class="ti tabler-check" style="font-size:.8rem"></i></button>
                </div>
            </div>
        </div>

        {{-- ── Customer Card ── --}}
        @if($customer && $customerStats)
        <div class="card sb-card mb-3">
            <div class="cust-header">
                <div class="avatar mx-auto mb-2">
                    @if($customer->avatar)
                        <img src="{{ asset($customer->avatar) }}" class="rounded-circle" alt="">
                    @else
                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($customer->name, 0, 2)) }}</span>
                    @endif
                </div>
                <h6 class="mb-0 fw-semibold" style="font-size:.85rem">{{ $customer->name }}</h6>
                <small class="text-muted" style="font-size:.72rem">{{ $customer->email }}</small>
                @if($customer->phone)<br><small class="text-muted" style="font-size:.68rem"><i class="ti tabler-phone me-1"></i>{{ $customer->phone }}</small>@endif
                <div class="mt-1"><small class="text-muted" style="font-size:.65rem"><i class="ti tabler-calendar me-1"></i>Member since {{ $customerStats['member_since']->format('M Y') }}</small></div>
            </div>
            <div class="cust-stats">
                <div class="cust-stat"><div class="cs-val text-primary">{{ $customerStats['total_orders'] }}</div><div class="cs-lbl">Orders</div></div>
                <div class="cust-stat"><div class="cs-val text-success">${{ number_format($customerStats['total_spent'], 0) }}</div><div class="cs-lbl">Spent</div></div>
                <div class="cust-stat"><div class="cs-val">{{ $customerStats['total_tickets'] }}</div><div class="cs-lbl">Tickets</div></div>
                <div class="cust-stat"><div class="cs-val {{ $customerStats['open_tickets'] > 0 ? 'text-warning' : 'text-success' }}">{{ $customerStats['open_tickets'] }}</div><div class="cs-lbl">Open</div></div>
            </div>
        </div>
        @endif

        {{-- ── Ticket Details Card ── --}}
        <div class="card sb-card mb-3">
            <div class="card-header"><h6><i class="ti tabler-info-circle me-1 text-primary"></i>Ticket Details</h6></div>
            <div class="card-body">
                <div class="sb-row">
                    <span class="sb-lbl">Department</span>
                    <span class="sb-val"><span class="badge bg-label-{{ $dColors[$supportTicket->department] ?? 'secondary' }}" style="font-size:.62rem">{{ ucfirst($supportTicket->department) }}</span></span>
                </div>
                <div class="sb-row">
                    <span class="sb-lbl">Priority</span>
                    <span class="sb-val">
                        <span class="badge bg-label-{{ $pColors[$supportTicket->priority] ?? 'secondary' }}" style="font-size:.62rem">
                            <i class="ti {{ $pIcons[$supportTicket->priority] ?? 'tabler-minus' }} me-1" style="font-size:.6rem"></i>{{ ucfirst($supportTicket->priority) }}
                        </span>
                    </span>
                </div>
                <div class="sb-row">
                    <span class="sb-lbl">Status</span>
                    <span class="sb-val">
                        <span class="badge bg-label-{{ $statusColors[$supportTicket->status] ?? 'secondary' }}" style="font-size:.62rem">
                            <i class="ti {{ $statusIcons[$supportTicket->status] ?? 'tabler-circle' }} me-1" style="font-size:.6rem"></i>{{ ucwords(str_replace('_', ' ', $supportTicket->status)) }}
                        </span>
                    </span>
                </div>
                @if($supportTicket->seller)
                <div class="sb-row">
                    <span class="sb-lbl">Seller</span>
                    <span class="sb-val"><a href="{{ route('sellers.show', $supportTicket->seller_id) }}" class="text-primary fw-semibold" style="font-size:.75rem"><i class="ti tabler-building-store me-1"></i>{{ Str::limit($supportTicket->seller->store_name, 18) }}</a></span>
                </div>
                @endif
                @if($supportTicket->assignedAdmin)
                <div class="sb-row">
                    <span class="sb-lbl">Assigned To</span>
                    <span class="sb-val" style="font-size:.78rem"><i class="ti tabler-shield me-1 text-primary" style="font-size:.7rem"></i>{{ $supportTicket->assignedAdmin->name }}</span>
                </div>
                @endif
                <div class="sb-row"><span class="sb-lbl">Created</span><span class="sb-val" style="font-size:.75rem">{{ $supportTicket->created_at->format('M d, Y H:i') }}</span></div>
                @if($supportTicket->last_reply_at)
                <div class="sb-row"><span class="sb-lbl">Last Reply</span><span class="sb-val" style="font-size:.75rem">{{ $supportTicket->last_reply_at->diffForHumans() }}</span></div>
                @endif
                @if($supportTicket->resolved_at)
                <div class="sb-row"><span class="sb-lbl">Resolved</span><span class="sb-val text-success" style="font-size:.75rem">{{ $supportTicket->resolved_at->format('M d, Y H:i') }}</span></div>
                @endif
                @if($supportTicket->closed_at)
                <div class="sb-row"><span class="sb-lbl">Closed</span><span class="sb-val" style="font-size:.75rem">{{ $supportTicket->closed_at->format('M d, Y H:i') }}</span></div>
                @endif
                @if($supportTicket->ip_address)
                <div class="sb-row"><span class="sb-lbl">IP Address</span><span class="sb-val" style="font-size:.72rem"><code>{{ $supportTicket->ip_address }}</code></span></div>
                @endif
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             ORDER DETAILS — Premium Sidebar Card
             ══════════════════════════════════════════ --}}
        @if($order)
        <div class="card sb-card order-detail-card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6><i class="ti tabler-shopping-cart me-1 text-primary"></i>Linked Order</h6>
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-xs btn-label-primary" style="font-size:.65rem;border-radius:4px"><i class="ti tabler-external-link me-1"></i>View Full</a>
            </div>
            <div class="card-body">
                {{-- Order Summary Grid --}}
                <div class="order-summary-grid">
                    <div class="order-summary-item">
                        <div class="osi-label">Order #</div>
                        <div class="osi-value text-primary">#{{ $order->order_number }}</div>
                    </div>
                    <div class="order-summary-item">
                        <div class="osi-label">Total</div>
                        <div class="osi-value text-success">${{ number_format($order->total_amount, 2) }}</div>
                    </div>
                    <div class="order-summary-item">
                        <div class="osi-label">Payment</div>
                        <div class="osi-value">
                            @php $payColor = match($order->payment_status) { 'paid'=>'success','pending'=>'warning','failed'=>'danger','refunded'=>'info', default=>'secondary' }; @endphp
                            <span class="badge bg-label-{{ $payColor }}" style="font-size:.62rem">{{ ucfirst($order->payment_status) }}</span>
                        </div>
                    </div>
                    <div class="order-summary-item">
                        <div class="osi-label">Status</div>
                        <div class="osi-value">
                            @php $oColor = match($order->status) { 'completed'=>'success','processing'=>'info','pending'=>'warning','cancelled'=>'danger', default=>'secondary' }; @endphp
                            <span class="badge bg-label-{{ $oColor }}" style="font-size:.62rem">{{ ucfirst($order->status) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Info --}}
                @if($order->payment_method || $order->payment_gateway)
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-1 mb-1" style="font-size:.68rem;font-weight:600;color:#a1acb8;text-transform:uppercase;letter-spacing:.4px">
                        <i class="ti tabler-credit-card" style="font-size:.72rem"></i> Payment Info
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        @if($order->payment_method)<span class="badge bg-label-secondary" style="font-size:.62rem">{{ ucfirst($order->payment_method) }}</span>@endif
                        @if($order->payment_gateway)<span class="badge bg-label-secondary" style="font-size:.62rem">{{ ucfirst($order->payment_gateway) }}</span>@endif
                        @if($order->payment_reference)<span class="badge bg-label-secondary" style="font-size:.6rem;font-family:monospace">{{ Str::limit($order->payment_reference, 20) }}</span>@endif
                    </div>
                </div>
                @endif

                {{-- Price Breakdown --}}
                @if($order->subtotal || $order->tax_amount || $order->discount_amount)
                <div class="mb-3" style="background:#f8f7fa;border-radius:8px;padding:10px">
                    @if($order->subtotal)
                    <div class="d-flex justify-content-between" style="font-size:.75rem;padding:2px 0"><span class="text-muted">Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                    @endif
                    @if($order->tax_amount && $order->tax_amount > 0)
                    <div class="d-flex justify-content-between" style="font-size:.75rem;padding:2px 0"><span class="text-muted">Tax</span><span>${{ number_format($order->tax_amount, 2) }}</span></div>
                    @endif
                    @if($order->discount_amount && $order->discount_amount > 0)
                    <div class="d-flex justify-content-between" style="font-size:.75rem;padding:2px 0"><span class="text-muted">Discount</span><span class="text-danger">-${{ number_format($order->discount_amount, 2) }}</span></div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold" style="font-size:.82rem;padding:4px 0;border-top:1px dashed rgba(0,0,0,.08);margin-top:4px"><span>Total</span><span class="text-success">${{ number_format($order->total_amount, 2) }}</span></div>
                </div>
                @endif

                {{-- Order Items --}}
                @if($order->items && $order->items->count())
                <div class="mb-2">
                    <div class="d-flex align-items-center gap-1 mb-2" style="font-size:.68rem;font-weight:600;color:#a1acb8;text-transform:uppercase;letter-spacing:.4px">
                        <i class="ti tabler-package" style="font-size:.72rem"></i> Items ({{ $order->items->count() }})
                    </div>
                    @foreach($order->items as $item)
                    <div class="order-item-card">
                        @if($item->product && $item->product->image)
                            <img src="{{ asset($item->product->image) }}" class="oic-img" alt="">
                        @else
                            <div class="oic-placeholder"><i class="ti tabler-box" style="font-size:.85rem"></i></div>
                        @endif
                        <div class="oic-info">
                            <div class="oic-title">{{ $item->product->title ?? 'Product' }}</div>
                            <div class="oic-meta">Qty: {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}</div>
                        </div>
                        <div class="oic-price">${{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Order Timeline --}}
                <div>
                    <div class="d-flex align-items-center gap-1 mb-2" style="font-size:.68rem;font-weight:600;color:#a1acb8;text-transform:uppercase;letter-spacing:.4px">
                        <i class="ti tabler-timeline" style="font-size:.72rem"></i> Timeline
                    </div>
                    <div class="order-timeline">
                        <div class="order-tl-item active">
                            <div class="otl-label">Order Placed</div>
                            <div class="otl-date">{{ $order->created_at->format('M d, Y H:i') }}</div>
                        </div>
                        @if($order->paid_at)
                        <div class="order-tl-item active">
                            <div class="otl-label text-success">Payment Received</div>
                            <div class="otl-date">{{ $order->paid_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                        @if($order->completed_at)
                        <div class="order-tl-item active">
                            <div class="otl-label text-success">Completed</div>
                            <div class="otl-date">{{ $order->completed_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                        @if($order->cancelled_at)
                        <div class="order-tl-item active">
                            <div class="otl-label text-danger">Cancelled</div>
                            <div class="otl-date">{{ $order->cancelled_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                        @if($order->refunded_at)
                        <div class="order-tl-item active">
                            <div class="otl-label text-warning">Refunded</div>
                            <div class="otl-date">{{ $order->refunded_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Previous Tickets ── --}}
        @if($previousTickets->count())
        <div class="card sb-card mb-3">
            <div class="card-header"><h6><i class="ti tabler-history me-1 text-primary"></i>Ticket History ({{ $previousTickets->count() }})</h6></div>
            <div class="card-body">
                @foreach($previousTickets as $pt)
                <div class="prev-item">
                    <div style="min-width:0">
                        <a href="{{ route('support-tickets.show', $pt->id) }}" class="fw-semibold text-primary" style="font-size:.75rem">{{ $pt->ticket_number }}</a>
                        <div class="text-truncate text-muted" style="font-size:.68rem">{{ Str::limit($pt->subject, 26) }}</div>
                    </div>
                    <span class="badge bg-label-{{ $statusColors[$pt->status] ?? 'secondary' }}" style="font-size:.55rem">{{ ucwords(str_replace('_', ' ', $pt->status)) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ═══════ Canned Responses Offcanvas ═══════ --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="cannedOffcanvas" style="width:380px">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title mb-0" style="font-size:.875rem"><i class="ti tabler-template me-2 text-primary"></i>Response Templates</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3 border-bottom">
            <input type="text" class="form-control form-control-sm" id="canned-search" placeholder="Search templates..." style="font-size:.82rem;border-radius:6px">
        </div>
        <div class="canned-list" id="canned-list">
            @php $groupedCanned = $cannedResponses->groupBy('category'); @endphp
            @foreach($groupedCanned as $cat => $items)
                <div class="px-3 py-1 bg-label-secondary" style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px">{{ ucfirst($cat) }}</div>
                @foreach($items as $cr)
                <div class="canned-item" data-shortcut="{{ $cr->shortcut }}" data-body="{{ $cr->body }}" data-title="{{ $cr->title }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="canned-title">{{ $cr->title }}</span>
                        @if($cr->shortcut)<span class="canned-shortcut">{{ $cr->shortcut }}</span>@endif
                    </div>
                    <div class="canned-preview">{{ Str::limit($cr->body, 85) }}</div>
                </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    window.ticketConfig = {
        csrfToken:    '{{ csrf_token() }}',
        ticketId:     {{ $supportTicket->id }},
        baseUrl:      '{{ url("dashboard/support-tickets") }}',
        replyUrl:     '{{ route("support-tickets.reply", $supportTicket->id) }}',
        customerName: @json($customer->name ?? 'Customer'),
        orderNumber:  @json($order?->order_number ?? ''),
        agentName:    @json(auth()->user()->name ?? 'Support Team'),
        cannedMap:    {!! json_encode($cannedResponses->whereNotNull('shortcut')->pluck('body', 'shortcut')) !!},
        aiEnabled:    {{ $aiEnabled ? 'true' : 'false' }},
        aiGenerateUrl: '{{ route("ai.generate") }}',
        aiContext: {
            subject:       @json($supportTicket->subject),
            department:    @json($supportTicket->department),
            priority:      @json($supportTicket->priority),
            customer_name: @json($customer->name ?? 'Customer'),
            messages:      @json($supportTicket->messages->map(fn($m) => ['role' => $m->sender_role, 'message' => \Illuminate\Support\Str::limit($m->message, 500)]))
        }
    };
</script>
<script src="{{ asset('assets/js/app-support-ticket-show.js') }}"></script>
@endpush
