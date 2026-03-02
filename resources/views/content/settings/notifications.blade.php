@extends('layouts.app')
@section('title', 'Notification Settings')
@include('content.settings.partials.settings-layout')

@section('content')
<div class="row">
    <div class="col-lg-3">
        @include('content.settings.partials.settings-nav')
    </div>
    <div class="col-lg-9">
        <div class="settings-header d-flex align-items-center gap-3">
            <div class="settings-header-icon"><i class="ti tabler-bell"></i></div>
            <div>
                <h4>Notification Settings</h4>
                <p>Configure email and system notifications for different events</p>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4 flex-nowrap overflow-auto" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-general" role="tab">
                    <i class="ti tabler-settings me-1"></i> General
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-order" role="tab">
                    <i class="ti tabler-shopping-cart me-1"></i> Order Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-ticket" role="tab">
                    <i class="ti tabler-ticket me-1"></i> Ticket Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-refund" role="tab">
                    <i class="ti tabler-receipt-refund me-1"></i> Refund Events
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-wallet" role="tab">
                    <i class="ti tabler-wallet me-1"></i> Wallet Events
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- General Tab --}}
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                <form id="generalNotifForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-mail text-primary me-2"></i>Admin Email</h5>
                            <p>Primary email address for admin notifications</p>
                        </div>
                        <div class="card-body row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Admin Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-at"></i></span>
                                    <input type="email" name="admin_email" class="form-control" value="{{ $generalSettings['admin_email'] ?? '' }}" placeholder="admin@yourstore.com">
                                </div>
                                <div class="form-label-description">All admin notifications will be sent to this address</div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-building-store text-primary me-2"></i>Seller Lifecycle</h5>
                            <p>Notifications sent to sellers on account status changes</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'seller_approved'    => ['Seller Approved', 'Notify seller when their application is approved'],
                                'seller_rejected'    => ['Seller Rejected', 'Notify seller when their application is rejected'],
                                'seller_suspended'   => ['Seller Suspended', 'Notify seller when their account is suspended'],
                                'seller_reactivated' => ['Seller Reactivated', 'Notify seller when their account is reactivated'],
                            ] as $key => [$label, $desc])
                            <div class="setting-toggle">
                                <div class="setting-toggle-info"><h6>{{ $label }}</h6><p>{{ $desc }}</p></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($generalSettings[$key] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-cash text-primary me-2"></i>Seller Withdrawals</h5>
                            <p>Notifications sent to sellers about withdrawal status</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'withdrawal_requested' => ['Withdrawal Submitted', 'Send confirmation when seller submits a withdrawal request'],
                                'withdrawal_approved'  => ['Withdrawal Approved', 'Notify seller when their withdrawal is approved'],
                                'withdrawal_rejected'  => ['Withdrawal Rejected', 'Notify seller when their withdrawal is rejected'],
                            ] as $key => [$label, $desc])
                            <div class="setting-toggle">
                                <div class="setting-toggle-info"><h6>{{ $label }}</h6><p>{{ $desc }}</p></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($generalSettings[$key] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-bell-ringing text-primary me-2"></i>Other Notifications</h5>
                            <p>Miscellaneous notification triggers</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'new_contact_message'  => ['New Contact Message', 'Notify admins when a visitor submits a contact form'],
                                'new_product_review'   => ['New Product Review', 'Notify seller when a customer leaves a product review'],
                                'product_request_status'=> ['Product Request Status', 'Notify user when their product request status changes'],
                                'subscriber_welcome'   => ['Subscriber Welcome', 'Send welcome email to new newsletter subscribers'],
                            ] as $key => [$label, $desc])
                            <div class="setting-toggle">
                                <div class="setting-toggle-info"><h6>{{ $label }}</h6><p>{{ $desc }}</p></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($generalSettings[$key] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Order Events Tab --}}
            <div class="tab-pane fade" id="tab-order" role="tabpanel">
                <form id="orderNotifForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-package text-primary me-2"></i>Order Placed</h5>
                            <p>Who gets notified when a new order is placed</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send order confirmation email to the customer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_placed" value="1" {{ ($orderSettings['customer_on_placed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Seller</h6>
                                    <p>Send new order notification to the seller</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="seller_on_placed" value="1" {{ ($orderSettings['seller_on_placed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Admin</h6>
                                    <p>Send new order notification to the admin</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_placed" value="1" {{ ($orderSettings['admin_on_placed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-credit-card text-primary me-2"></i>Order Paid</h5>
                            <p>Who gets notified when payment is received</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send payment confirmation email to the customer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_paid" value="1" {{ ($orderSettings['customer_on_paid'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Admin</h6>
                                    <p>Send payment received notification to the admin</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_paid" value="1" {{ ($orderSettings['admin_on_paid'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-refresh text-primary me-2"></i>Status Changes</h5>
                            <p>Notifications for order status transitions</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Customer on Status Change</h6>
                                    <p>Notify customer when their order status changes</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_status_change" value="1" {{ ($orderSettings['customer_on_status_change'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-circle-check text-primary me-2"></i>Order Completed</h5>
                            <p>Who gets notified when an order is completed</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send completion confirmation to the customer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_completed" value="1" {{ ($orderSettings['customer_on_completed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Seller</h6>
                                    <p>Send completion notification to the seller</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="seller_on_completed" value="1" {{ ($orderSettings['seller_on_completed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-x text-primary me-2"></i>Order Cancelled</h5>
                            <p>Who gets notified when an order is cancelled</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send cancellation notification to the customer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_cancelled" value="1" {{ ($orderSettings['customer_on_cancelled'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Seller</h6>
                                    <p>Send cancellation notification to the seller</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="seller_on_cancelled" value="1" {{ ($orderSettings['seller_on_cancelled'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Admin</h6>
                                    <p>Send cancellation notification to the admin</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_cancelled" value="1" {{ ($orderSettings['admin_on_cancelled'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-receipt-refund text-primary me-2"></i>Order Refunded</h5>
                            <p>Who gets notified when a refund is processed</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send refund confirmation to the customer</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_refunded" value="1" {{ ($orderSettings['customer_on_refunded'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Seller</h6>
                                    <p>Send refund notification to the seller</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="seller_on_refunded" value="1" {{ ($orderSettings['seller_on_refunded'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-package text-primary me-2"></i>Order Delivery</h5>
                            <p>Notifications for digital product delivery</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer on Delivery</h6>
                                    <p>Send email when product keys/licenses are delivered</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_delivery" value="1" {{ ($orderSettings['customer_on_delivery'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Admin on Delivery Failed</h6>
                                    <p>Notify admins when auto-delivery fails</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_delivery_failed" value="1" {{ ($orderSettings['admin_on_delivery_failed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Ticket Events Tab --}}
            <div class="tab-pane fade" id="tab-ticket" role="tabpanel">
                <form id="ticketNotifForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-ticket text-primary me-2"></i>Ticket Lifecycle</h5>
                            <p>Notifications for ticket creation and closure</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Ticket Created</h6>
                                    <p>Notify the customer when their ticket is created</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_ticket_created" value="1" {{ ($ticketSettings['on_ticket_created'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Admin on New Ticket</h6>
                                    <p>Notify admin when a new support ticket is submitted</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_new_ticket" value="1" {{ ($ticketSettings['admin_on_new_ticket'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Ticket Closed</h6>
                                    <p>Notify when a ticket is closed</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_ticket_closed" value="1" {{ ($ticketSettings['on_ticket_closed'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-message text-primary me-2"></i>Replies</h5>
                            <p>Notifications for ticket replies</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Staff Reply</h6>
                                    <p>Notify the customer when staff replies to their ticket</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_staff_reply" value="1" {{ ($ticketSettings['on_staff_reply'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Customer Reply</h6>
                                    <p>Notify assigned staff when the customer replies</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_customer_reply" value="1" {{ ($ticketSettings['on_customer_reply'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-arrows-transfer-up text-primary me-2"></i>Assignment & Escalation</h5>
                            <p>Notifications for ticket routing changes</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Status Change</h6>
                                    <p>Notify when a ticket's status is updated</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_status_change" value="1" {{ ($ticketSettings['on_status_change'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Assigned</h6>
                                    <p>Notify staff when a ticket is assigned to them</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_assigned" value="1" {{ ($ticketSettings['on_assigned'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>On Escalated</h6>
                                    <p>Notify when a ticket is escalated to a higher priority</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="on_escalated" value="1" {{ ($ticketSettings['on_escalated'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Refund Events Tab --}}
            <div class="tab-pane fade" id="tab-refund" role="tabpanel">
                <form id="refundNotifForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-receipt-refund text-primary me-2"></i>Refund Requests</h5>
                            <p>Who gets notified when a refund is requested</p>
                        </div>
                        <div class="card-body">
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Customer</h6>
                                    <p>Send confirmation when customer submits a refund request</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="customer_on_requested" value="1" {{ ($refundSettings['customer_on_requested'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="setting-toggle">
                                <div class="setting-toggle-info">
                                    <h6>Notify Admin</h6>
                                    <p>Alert admins when a new refund request is submitted</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="admin_on_requested" value="1" {{ ($refundSettings['admin_on_requested'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-circle-check text-primary me-2"></i>Refund Status</h5>
                            <p>Customer notifications for refund status changes</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'customer_on_approved'  => ['Refund Approved', 'Notify customer when their refund request is approved'],
                                'customer_on_rejected'  => ['Refund Rejected', 'Notify customer when their refund request is rejected'],
                                'customer_on_completed' => ['Refund Completed', 'Notify customer when their refund is processed and credited'],
                            ] as $key => [$label, $desc])
                            <div class="setting-toggle">
                                <div class="setting-toggle-info"><h6>{{ $label }}</h6><p>{{ $desc }}</p></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($refundSettings[$key] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            {{-- Wallet Events Tab --}}
            <div class="tab-pane fade" id="tab-wallet" role="tabpanel">
                <form id="walletNotifForm">
                    @csrf
                    @method('PUT')

                    <div class="card setting-card">
                        <div class="card-header">
                            <h5><i class="ti tabler-wallet text-primary me-2"></i>Wallet Transactions</h5>
                            <p>Notifications for wallet-related events</p>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'on_deposit_confirmed' => ['Deposit Confirmed', 'Notify user when a wallet deposit is confirmed'],
                                'on_transfer_received' => ['Transfer Received', 'Notify user when they receive a wallet transfer'],
                                'on_seller_transfer'   => ['Seller Balance Transfer', 'Notify seller when earnings are transferred to their wallet'],
                            ] as $key => [$label, $desc])
                            <div class="setting-toggle">
                                <div class="setting-toggle-info"><h6>{{ $label }}</h6><p>{{ $desc }}</p></div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($walletSettings[$key] ?? false) ? 'checked' : '' }}>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="save-bar">
                        <button type="button" class="btn btn-label-secondary" onclick="location.reload()">Discard</button>
                        <button type="submit" class="btn btn-primary"><i class="ti tabler-device-floppy me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
saveSettings('generalNotifForm', '{{ route("settings.notifications.update", "general") }}');
saveSettings('orderNotifForm', '{{ route("settings.notifications.update", "order") }}');
saveSettings('ticketNotifForm', '{{ route("settings.notifications.update", "ticket") }}');
saveSettings('refundNotifForm', '{{ route("settings.notifications.update", "refund") }}');
saveSettings('walletNotifForm', '{{ route("settings.notifications.update", "wallet") }}');
</script>
@endpush
