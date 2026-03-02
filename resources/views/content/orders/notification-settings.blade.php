@extends('layouts.app')

@section('title', 'Order Notification Settings')

@push('page-css')
<style>
.notif-card { border: none; box-shadow: 0 2px 6px rgba(0,0,0,.06); border-radius: .5rem; }
.notif-item { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid rgba(0,0,0,.04); transition: background .15s; }
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: rgba(115,103,240,.02); }
.notif-item .notif-info h6 { font-size: .88rem; margin-bottom: 2px; }
.notif-item .notif-info p { font-size: .78rem; color: #a1acb8; margin: 0; }
.notif-item .notif-icon { width: 40px; height: 40px; border-radius: .5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notif-group-header { background: #f5f5f9; padding: .6rem 1.25rem; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #a1acb8; }
.recipient-badge { font-size: .65rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('orders.index') }}" class="text-muted d-inline-block mb-1" style="font-size:.82rem">
            <i class="ti tabler-arrow-left me-1"></i>Back to Orders
        </a>
        <h4 class="mb-0"><i class="ti tabler-bell-cog me-1"></i> Order Notification Settings</h4>
        <p class="text-muted mb-0 mt-1">Control email notifications for order lifecycle events</p>
    </div>
    <button class="btn btn-primary" id="btn-save">
        <i class="ti tabler-check me-1"></i> Save Settings
    </button>
</div>

<form id="notif-form">
    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Order Placed --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-shopping-cart me-1"></i> Order Placed</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-mail-check"></i></div>
                        <div class="notif-info">
                            <h6>Customer Order Confirmation <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Send confirmation email to the customer when they place an order</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_placed" id="customer_on_placed" {{ !empty($settings['customer_on_placed']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-success"><i class="ti tabler-building-store"></i></div>
                        <div class="notif-info">
                            <h6>Seller New Order Alert <span class="recipient-badge bg-label-success">Seller</span></h6>
                            <p>Notify sellers when they receive a new order containing their products</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="seller_on_placed" id="seller_on_placed" {{ !empty($settings['seller_on_placed']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-danger"><i class="ti tabler-shield"></i></div>
                        <div class="notif-info">
                            <h6>Admin New Order Alert <span class="recipient-badge bg-label-danger">Admin</span></h6>
                            <p>Notify all admin users when a new order is placed on the platform</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="admin_on_placed" id="admin_on_placed" {{ !empty($settings['admin_on_placed']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-credit-card me-1"></i> Payment Events</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-receipt-2"></i></div>
                        <div class="notif-info">
                            <h6>Customer Payment Confirmation <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Notify the customer when payment is confirmed for their order</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_paid" id="customer_on_paid" {{ !empty($settings['customer_on_paid']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-danger"><i class="ti tabler-cash-register"></i></div>
                        <div class="notif-info">
                            <h6>Admin Payment Alert <span class="recipient-badge bg-label-danger">Admin</span></h6>
                            <p>Notify admins when a payment is confirmed</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="admin_on_paid" id="admin_on_paid" {{ !empty($settings['admin_on_paid']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Status Changes --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-refresh me-1"></i> Status Change Events</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-info"><i class="ti tabler-loader"></i></div>
                        <div class="notif-info">
                            <h6>Customer Status Update <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Notify the customer when order status changes (e.g. pending to processing)</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_status_change" id="customer_on_status_change" {{ !empty($settings['customer_on_status_change']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Completed --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-circle-check me-1"></i> Order Completed</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-package"></i></div>
                        <div class="notif-info">
                            <h6>Customer Completion Email <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Notify the customer when their order is completed and items are delivered</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_completed" id="customer_on_completed" {{ !empty($settings['customer_on_completed']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-success"><i class="ti tabler-rosette-discount-check"></i></div>
                        <div class="notif-info">
                            <h6>Seller Order Completed <span class="recipient-badge bg-label-success">Seller</span></h6>
                            <p>Notify sellers when an order containing their products is completed</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="seller_on_completed" id="seller_on_completed" {{ !empty($settings['seller_on_completed']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Cancelled --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-x me-1"></i> Order Cancelled</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-circle-x"></i></div>
                        <div class="notif-info">
                            <h6>Customer Cancellation Email <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Notify the customer when their order is cancelled</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_cancelled" id="customer_on_cancelled" {{ !empty($settings['customer_on_cancelled']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-success"><i class="ti tabler-alert-circle"></i></div>
                        <div class="notif-info">
                            <h6>Seller Cancellation Alert <span class="recipient-badge bg-label-success">Seller</span></h6>
                            <p>Notify sellers when an order containing their products is cancelled</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="seller_on_cancelled" id="seller_on_cancelled" {{ !empty($settings['seller_on_cancelled']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-danger"><i class="ti tabler-alert-triangle"></i></div>
                        <div class="notif-info">
                            <h6>Admin Cancellation Alert <span class="recipient-badge bg-label-danger">Admin</span></h6>
                            <p>Notify admins when any order is cancelled</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="admin_on_cancelled" id="admin_on_cancelled" {{ !empty($settings['admin_on_cancelled']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- Refunded --}}
            <div class="card notif-card mb-4">
                <div class="notif-group-header"><i class="ti tabler-receipt-refund me-1"></i> Order Refunded</div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-primary"><i class="ti tabler-arrow-back-up"></i></div>
                        <div class="notif-info">
                            <h6>Customer Refund Email <span class="recipient-badge bg-label-primary">Customer</span></h6>
                            <p>Notify the customer when a refund is issued for their order</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="customer_on_refunded" id="customer_on_refunded" {{ !empty($settings['customer_on_refunded']) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="d-flex align-items-start gap-3">
                        <div class="notif-icon bg-label-success"><i class="ti tabler-receipt-off"></i></div>
                        <div class="notif-info">
                            <h6>Seller Refund Alert <span class="recipient-badge bg-label-success">Seller</span></h6>
                            <p>Notify sellers when an order containing their products is refunded</p>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-1">
                        <input class="form-check-input" type="checkbox" name="seller_on_refunded" id="seller_on_refunded" {{ !empty($settings['seller_on_refunded']) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            {{-- Info card --}}
            <div class="card notif-card mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="ti tabler-info-circle me-1 text-primary"></i> How It Works</h6></div>
                <div class="card-body" style="font-size:.84rem">
                    <p class="mb-2">Emails are sent using your configured <strong>SMTP settings</strong>. Make sure mail is properly configured in your <strong>Settings</strong> page.</p>
                    <p class="mb-2">Each toggle controls a specific event and recipient. Recipients are grouped by role:</p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="recipient-badge bg-label-primary">Customer</span>
                        <span class="recipient-badge bg-label-success">Seller</span>
                        <span class="recipient-badge bg-label-danger">Admin</span>
                    </div>
                    <p class="mb-0">All notifications are <strong>queued</strong> for background processing and fail silently if SMTP is not configured.</p>
                </div>
            </div>

            {{-- Summary card --}}
            <div class="card notif-card mb-4">
                <div class="card-header"><h6 class="mb-0"><i class="ti tabler-list-check me-1 text-primary"></i> Notification Summary</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.82rem">
                        <thead class="table-light"><tr><th>Event</th><th>Recipients</th></tr></thead>
                        <tbody>
                            <tr><td>Order placed</td><td><span class="recipient-badge bg-label-primary">C</span> <span class="recipient-badge bg-label-success">S</span> <span class="recipient-badge bg-label-danger">A</span></td></tr>
                            <tr><td>Payment confirmed</td><td><span class="recipient-badge bg-label-primary">C</span> <span class="recipient-badge bg-label-danger">A</span></td></tr>
                            <tr><td>Status change</td><td><span class="recipient-badge bg-label-primary">C</span></td></tr>
                            <tr><td>Order completed</td><td><span class="recipient-badge bg-label-primary">C</span> <span class="recipient-badge bg-label-success">S</span></td></tr>
                            <tr><td>Order cancelled</td><td><span class="recipient-badge bg-label-primary">C</span> <span class="recipient-badge bg-label-success">S</span> <span class="recipient-badge bg-label-danger">A</span></td></tr>
                            <tr><td>Order refunded</td><td><span class="recipient-badge bg-label-primary">C</span> <span class="recipient-badge bg-label-success">S</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick legend --}}
            <div class="card notif-card">
                <div class="card-header"><h6 class="mb-0"><i class="ti tabler-chart-dots me-1 text-primary"></i> Order Lifecycle</h6></div>
                <div class="card-body" style="font-size:.82rem">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-label-warning" style="width:80px">Pending</span>
                        <i class="ti tabler-arrow-right text-muted ti-xs"></i>
                        <span class="badge bg-label-info" style="width:80px">Processing</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-label-info" style="width:80px">Processing</span>
                        <i class="ti tabler-arrow-right text-muted ti-xs"></i>
                        <span class="badge bg-label-success" style="width:80px">Completed</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-label-warning" style="width:80px">Any</span>
                        <i class="ti tabler-arrow-right text-muted ti-xs"></i>
                        <span class="badge bg-label-danger" style="width:80px">Cancelled</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-success" style="width:80px">Completed</span>
                        <i class="ti tabler-arrow-right text-muted ti-xs"></i>
                        <span class="badge bg-label-secondary" style="width:80px">Refunded</span>
                    </div>
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
    var saveUrl   = '{{ route("order-notification-settings.update") }}';

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
