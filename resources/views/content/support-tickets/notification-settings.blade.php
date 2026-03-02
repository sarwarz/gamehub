@extends('layouts.app')

@section('title', 'Ticket Notification Settings')

@push('page-css')
<style>
.notif-card { border: none; box-shadow: 0 2px 6px rgba(0,0,0,.06); border-radius: .5rem; }
.notif-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.04); }
.notif-item:last-child { border-bottom: none; }
.notif-item .notif-info h6 { font-size: .88rem; margin-bottom: 2px; }
.notif-item .notif-info p { font-size: .78rem; color: #a1acb8; margin: 0; }
.notif-item .notif-icon { width: 40px; height: 40px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-group-header { background: #f5f5f9; padding: .6rem 1.25rem; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #a1acb8; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('support-tickets.index') }}" class="text-muted d-inline-block mb-1" style="font-size:.82rem">
            <i class="ti tabler-arrow-left me-1"></i>Back to Tickets
        </a>
        <h4 class="mb-0"><i class="ti tabler-bell-cog me-1"></i> Notification Settings</h4>
        <p class="text-muted mb-0 mt-1">Control which email notifications are sent for support ticket events</p>
    </div>
    <button class="btn btn-primary" id="btn-save">
        <i class="ti tabler-check me-1"></i> Save Settings
    </button>
</div>

<form id="notif-form">
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- New Ticket --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-ticket me-1"></i> New Ticket Events</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-ticket"></i></div>
                        <div class="notif-info">
                            <h6>Enable New Ticket Notifications</h6>
                            <p>Master toggle — enables all notifications when a new ticket is created</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_ticket_created" id="on_ticket_created" {{ !empty($settings['on_ticket_created']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-danger"><i class="ti tabler-shield"></i></div>
                        <div class="notif-info">
                            <h6>Notify Admins on New Ticket</h6>
                            <p>Send email to all admin users when a customer creates a new support ticket</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="admin_on_new_ticket" id="admin_on_new_ticket" {{ !empty($settings['admin_on_new_ticket']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Reply Events --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-message me-1"></i> Reply Events</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-success"><i class="ti tabler-send"></i></div>
                        <div class="notif-info">
                            <h6>Customer Notification on Staff Reply</h6>
                            <p>Email the customer when an admin or seller replies to their ticket</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_staff_reply" id="on_staff_reply" {{ !empty($settings['on_staff_reply']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-info"><i class="ti tabler-message-reply"></i></div>
                        <div class="notif-info">
                            <h6>Staff Notification on Customer Reply</h6>
                            <p>Email the assigned admin (or all admins) when a customer adds a reply</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_customer_reply" id="on_customer_reply" {{ !empty($settings['on_customer_reply']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Status & Assignment --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-settings me-1"></i> Status & Assignment Events</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-warning"><i class="ti tabler-refresh"></i></div>
                        <div class="notif-info">
                            <h6>Customer Notification on Status Change</h6>
                            <p>Email the customer when ticket status changes to resolved, closed, or awaiting customer</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_status_change" id="on_status_change" {{ !empty($settings['on_status_change']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-secondary"><i class="ti tabler-circle-x"></i></div>
                        <div class="notif-info">
                            <h6>Customer Notification on Ticket Closed</h6>
                            <p>Email the customer when their ticket is closed by an admin</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_ticket_closed" id="on_ticket_closed" {{ !empty($settings['on_ticket_closed']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-user-check"></i></div>
                        <div class="notif-info">
                            <h6>Admin Notification on Assignment</h6>
                            <p>Email the admin when a ticket is assigned to them</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_assigned" id="on_assigned" {{ !empty($settings['on_assigned']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-danger"><i class="ti tabler-alert-triangle"></i></div>
                        <div class="notif-info">
                            <h6>Admin Notification on Escalation</h6>
                            <p>Email all admins when a ticket is escalated from seller to admin</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="on_escalated" id="on_escalated" {{ !empty($settings['on_escalated']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Info card --}}
            <div class="card notif-card mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="ti tabler-info-circle me-1 text-primary"></i> How It Works</h6></div>
                <div class="card-body" style="font-size:.84rem">
                    <p class="mb-2">Email notifications are sent using your configured <strong>SMTP settings</strong> from the Settings page.</p>
                    <p class="mb-2">Each toggle controls a specific event. Disable any toggle to stop that email from being sent.</p>
                    <p class="mb-0">Notifications are wrapped in try/catch — if SMTP is not configured, they fail silently without affecting ticket operations.</p>
                </div>
            </div>

            {{-- Summary card --}}
            <div class="card notif-card">
                <div class="card-header"><h6 class="mb-0"><i class="ti tabler-list-check me-1 text-primary"></i> Notification Summary</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.82rem">
                        <thead class="table-light"><tr><th>Event</th><th>Recipient</th></tr></thead>
                        <tbody>
                            <tr><td>New ticket</td><td>Admins</td></tr>
                            <tr><td>Staff reply</td><td>Customer</td></tr>
                            <tr><td>Customer reply</td><td>Assigned admin / All admins</td></tr>
                            <tr><td>Status change</td><td>Customer</td></tr>
                            <tr><td>Ticket closed</td><td>Customer</td></tr>
                            <tr><td>Assignment</td><td>Assigned admin</td></tr>
                            <tr><td>Escalation</td><td>All admins</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('page-js')
<script>
$(function() {
    var csrfToken = '{{ csrf_token() }}';
    var saveUrl   = '{{ route("ticket-notification-settings.update") }}';

    $('#btn-save').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        var data = { _token: csrfToken };
        $('#notif-form input[type=checkbox]').each(function() {
            data[this.name] = this.checked ? 1 : 0;
        });

        $.ajax({
            url: saveUrl, type: 'PUT', data: data,
            success: function(res) {
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Save Settings');
            }
        });
    });
});
</script>
@endpush
