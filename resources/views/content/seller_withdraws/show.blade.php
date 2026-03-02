@extends('layouts.app')
@section('title', 'Withdrawal #' . $withdraw->id)

@push('page-css')
<style>
.withdraw-status-banner {
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.withdraw-status-banner.pending { background: rgba(255, 159, 67, .1); border: 1px solid rgba(255, 159, 67, .2); }
.withdraw-status-banner.approved { background: rgba(40, 199, 111, .1); border: 1px solid rgba(40, 199, 111, .2); }
.withdraw-status-banner.rejected { background: rgba(234, 84, 85, .1); border: 1px solid rgba(234, 84, 85, .2); }
.withdraw-status-banner.cancelled { background: rgba(168, 170, 174, .1); border: 1px solid rgba(168, 170, 174, .2); }
.withdraw-status-banner .status-icon {
    width: 48px; height: 48px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.withdraw-status-banner.pending .status-icon { background: rgba(255, 159, 67, .15); color: #ff9f43; }
.withdraw-status-banner.approved .status-icon { background: rgba(40, 199, 111, .15); color: #28c76f; }
.withdraw-status-banner.rejected .status-icon { background: rgba(234, 84, 85, .15); color: #ea5455; }
.withdraw-status-banner.cancelled .status-icon { background: rgba(168, 170, 174, .15); color: #a8aaae; }
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid var(--bs-border-color);
}
.detail-row:last-child { border-bottom: none; }
.detail-row .detail-label {
    color: var(--bs-secondary-color);
    font-size: .85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-row .detail-value {
    font-weight: 600;
    font-size: .85rem;
    text-align: right;
}
.payment-info-card {
    border-radius: 10px;
    border: 1px solid var(--bs-border-color);
    overflow: hidden;
}
.payment-info-card .payment-header {
    padding: .75rem 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}
.payment-info-card .payment-header.paypal { background: rgba(0, 119, 181, .08); color: #0077b5; }
.payment-info-card .payment-header.bank { background: rgba(115, 103, 240, .08); color: #7367f0; }
.payment-info-card .payment-header.crypto { background: rgba(255, 159, 67, .08); color: #ff9f43; }
.payment-info-card .payment-header.bkash { background: rgba(234, 53, 113, .08); color: #e33571; }
.payment-info-card .payment-header.nagad { background: rgba(255, 103, 31, .08); color: #ff671f; }
.payment-info-card .payment-header.wise { background: rgba(0, 182, 122, .08); color: #00b67a; }
.payment-info-card .payment-header.payoneer { background: rgba(255, 68, 0, .08); color: #ff4400; }
.payment-info-card .payment-header.skrill { background: rgba(131, 41, 131, .08); color: #832982; }
.payment-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 1rem;
    border-top: 1px solid var(--bs-border-color);
    font-size: .85rem;
}
.payment-info-item .info-label { color: var(--bs-secondary-color); }
.payment-info-item .info-value {
    font-weight: 600;
    font-family: 'SFMono-Regular', Consolas, monospace;
    word-break: break-all;
    max-width: 60%;
    text-align: right;
}
.payment-info-item .info-value .copy-btn {
    cursor: pointer;
    color: #7367f0;
    margin-left: 6px;
    font-size: .75rem;
}
.payment-info-item .info-value .copy-btn:hover { color: #5e50ee; }
.seller-card-avatar {
    width: 60px; height: 60px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid var(--bs-border-color);
}
.action-card {
    border: 2px dashed var(--bs-border-color);
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
}
.action-card.approve-zone:hover { border-color: #28c76f; background: rgba(40, 199, 111, .03); }
.action-card.reject-zone:hover { border-color: #ea5455; background: rgba(234, 84, 85, .03); }
.timeline-item {
    position: relative;
    padding-left: 28px;
    padding-bottom: 1.25rem;
}
.timeline-item:before {
    content: '';
    position: absolute;
    left: 8px;
    top: 24px;
    bottom: 0;
    width: 2px;
    background: var(--bs-border-color);
}
.timeline-item:last-child:before { display: none; }
.timeline-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .6rem;
}
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-file-invoice me-2"></i>Withdrawal Request #{{ $withdraw->id }}</h4>
            <p class="text-muted mb-0">Review and process this withdrawal request</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('seller-withdraws.index') }}" class="btn btn-label-secondary">
                <i class="ti tabler-arrow-left me-1"></i> Back to List
            </a>
            @if($withdraw->status === 'pending')
            <a href="{{ route('seller-withdraws.pending') }}" class="btn btn-label-warning">
                <i class="ti tabler-clock me-1"></i> Pending Queue
            </a>
            @endif
        </div>
    </div>

    {{-- Status Banner --}}
    @php
        $statusConfig = [
            'pending'   => ['icon' => 'tabler-clock', 'label' => 'Pending Review', 'desc' => 'This withdrawal is awaiting admin review and processing.'],
            'approved'  => ['icon' => 'tabler-circle-check', 'label' => 'Approved & Paid', 'desc' => 'This withdrawal has been processed and payment sent.'],
            'rejected'  => ['icon' => 'tabler-circle-x', 'label' => 'Rejected', 'desc' => 'This withdrawal request was rejected. Funds have been returned.'],
            'cancelled' => ['icon' => 'tabler-ban', 'label' => 'Cancelled', 'desc' => 'This withdrawal was cancelled by the seller.'],
        ];
        $sc = $statusConfig[$withdraw->status] ?? $statusConfig['pending'];
    @endphp
    <div class="withdraw-status-banner {{ $withdraw->status }} mb-4">
        <div class="status-icon"><i class="ti {{ $sc['icon'] }}" style="font-size: 1.4rem"></i></div>
        <div>
            <h6 class="mb-0">{{ $sc['label'] }}</h6>
            <small class="text-muted">{{ $sc['desc'] }}</small>
        </div>
    </div>

    <div class="row">
        {{-- LEFT COLUMN --}}
        <div class="col-12 col-lg-8">

            {{-- Withdrawal Details --}}
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-cash me-2 text-primary"></i>Withdrawal Details</h6>
                </div>
                <div class="card-body">
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-hash ti-xs"></i> Request ID</span>
                        <span class="detail-value">#{{ $withdraw->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-currency-dollar ti-xs"></i> Amount</span>
                        <span class="detail-value fs-5 text-primary">{{ format_currency($withdraw->amount) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-credit-card ti-xs"></i> Payment Method</span>
                        <span class="detail-value">
                            <span class="badge bg-label-info">{{ $withdraw->method_label }}</span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-calendar ti-xs"></i> Requested</span>
                        <span class="detail-value">{{ $withdraw->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($withdraw->processed_at)
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-calendar-check ti-xs"></i> Processed</span>
                        <span class="detail-value">{{ $withdraw->processed_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif
                    @if($withdraw->transaction_id)
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-receipt ti-xs"></i> Transaction ID</span>
                        <span class="detail-value font-monospace">{{ $withdraw->transaction_id }}</span>
                    </div>
                    @endif
                    @if($withdraw->note)
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-note ti-xs"></i> Seller Note</span>
                        <span class="detail-value text-wrap" style="max-width: 60%">{{ $withdraw->note }}</span>
                    </div>
                    @endif
                    @if($withdraw->admin_note)
                    <div class="detail-row">
                        <span class="detail-label"><i class="ti tabler-message ti-xs"></i> Admin Note</span>
                        <span class="detail-value text-wrap" style="max-width: 60%">{{ $withdraw->admin_note }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Payment Information --}}
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-wallet me-2 text-primary"></i>Payment Information</h6>
                </div>
                <div class="card-body">
                    @if(!empty($withdraw->payment_details))
                    <div class="payment-info-card">
                        @php
                            $methodIcons = [
                                'paypal' => 'brand-paypal', 'bank' => 'building-bank', 'crypto' => 'currency-bitcoin',
                                'bkash' => 'device-mobile', 'nagad' => 'device-mobile', 'wise' => 'arrows-exchange',
                                'payoneer' => 'credit-card', 'skrill' => 'wallet',
                            ];
                            $methodIcon = $methodIcons[$withdraw->method] ?? 'cash';
                            $friendlyLabels = [
                                'email' => 'Email Address', 'bank_name' => 'Bank Name', 'account_name' => 'Account Holder',
                                'account_number' => 'Account Number / IBAN', 'routing_number' => 'SWIFT / BIC / Routing',
                                'branch_name' => 'Branch Name', 'network' => 'Network', 'wallet_address' => 'Wallet Address',
                                'phone' => 'Phone Number', 'account_type' => 'Account Type', 'currency' => 'Currency',
                            ];
                        @endphp
                        <div class="payment-header {{ $withdraw->method }}">
                            <i class="ti tabler-{{ $methodIcon }}" style="font-size: 1.2rem"></i>
                            {{ $withdraw->method_label }} Details
                        </div>
                        @foreach($withdraw->payment_details as $key => $value)
                            @if(!empty($value))
                            <div class="payment-info-item">
                                <span class="info-label">{{ $friendlyLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}</span>
                                <span class="info-value">
                                    {{ $value }}
                                    <i class="ti tabler-copy copy-btn" data-value="{{ $value }}" title="Copy"></i>
                                </span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="mb-2"><i class="ti tabler-info-circle text-muted" style="font-size: 2rem"></i></div>
                        <p class="text-muted mb-0">No payment details provided.<br><small>This withdrawal was submitted before the payment details feature was added.</small></p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Process Actions (only for pending) --}}
            @if($withdraw->status === 'pending')
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-settings me-2 text-primary"></i>Process Withdrawal</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Approve --}}
                        <div class="col-md-6">
                            <div class="action-card approve-zone">
                                <div class="mb-3">
                                    <div class="avatar avatar-md bg-label-success mx-auto mb-2">
                                        <i class="ti tabler-check fs-4"></i>
                                    </div>
                                    <h6 class="mb-1">Approve & Send Payment</h6>
                                    <small class="text-muted">Mark as paid after sending money to the seller</small>
                                </div>
                                <div class="text-start mb-3">
                                    <label class="form-label small">Transaction ID / Reference</label>
                                    <input type="text" class="form-control form-control-sm" id="approve-txn-id"
                                           placeholder="e.g. TXN-123456 or PayPal transaction ID">
                                </div>
                                <div class="text-start mb-3">
                                    <label class="form-label small">Admin Note (optional)</label>
                                    <textarea class="form-control form-control-sm" id="approve-note" rows="2"
                                              placeholder="Internal note about the payment..."></textarea>
                                </div>
                                <button type="button" class="btn btn-success w-100" id="btn-approve">
                                    <i class="ti tabler-check me-1"></i> Approve & Mark as Paid
                                </button>
                            </div>
                        </div>

                        {{-- Reject --}}
                        <div class="col-md-6">
                            <div class="action-card reject-zone">
                                <div class="mb-3">
                                    <div class="avatar avatar-md bg-label-danger mx-auto mb-2">
                                        <i class="ti tabler-x fs-4"></i>
                                    </div>
                                    <h6 class="mb-1">Reject & Refund Balance</h6>
                                    <small class="text-muted">Funds will be returned to the seller's available balance</small>
                                </div>
                                <div class="text-start mb-3">
                                    <label class="form-label small">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm" id="reject-reason" rows="3"
                                              placeholder="Explain why this withdrawal is being rejected..."></textarea>
                                </div>
                                <button type="button" class="btn btn-danger w-100" id="btn-reject">
                                    <i class="ti tabler-x me-1"></i> Reject & Return Funds
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-12 col-lg-4">

            {{-- Seller Info --}}
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-user me-2 text-primary"></i>Seller Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $withdraw->seller?->logo ? asset($withdraw->seller->logo) : asset('assets/img/avatars/1.png') }}"
                             alt="Seller" class="seller-card-avatar">
                        <div>
                            <h6 class="mb-0">{{ $withdraw->seller?->store_name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $withdraw->seller?->user?->email ?? 'N/A' }}</small>
                        </div>
                    </div>
                    @if($withdraw->seller?->balance)
                    @php $bal = $withdraw->seller->balance; @endphp
                    <hr class="my-3">
                    <div class="detail-row">
                        <span class="detail-label">Available Balance</span>
                        <span class="detail-value text-success">{{ format_currency($bal->available_balance) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Pending Balance</span>
                        <span class="detail-value text-warning">{{ format_currency($bal->pending_balance) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Earned</span>
                        <span class="detail-value">{{ format_currency($bal->total_earned) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Paid</span>
                        <span class="detail-value">{{ format_currency($bal->total_paid) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-timeline me-2 text-primary"></i>Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-item">
                        <div class="timeline-dot bg-label-primary"><i class="ti tabler-plus"></i></div>
                        <div>
                            <strong class="small">Request Submitted</strong>
                            <div class="text-muted small">{{ $withdraw->created_at->format('d M Y, h:i A') }}</div>
                            <small class="text-muted">{{ format_currency($withdraw->amount) }} via {{ $withdraw->method_label }}</small>
                        </div>
                    </div>

                    @if($withdraw->status === 'approved')
                    <div class="timeline-item">
                        <div class="timeline-dot bg-label-success"><i class="ti tabler-check"></i></div>
                        <div>
                            <strong class="small">Approved & Paid</strong>
                            <div class="text-muted small">{{ $withdraw->processed_at ? $withdraw->processed_at->format('d M Y, h:i A') : $withdraw->updated_at->format('d M Y, h:i A') }}</div>
                            @if($withdraw->transaction_id)
                            <small class="text-muted font-monospace">Ref: {{ $withdraw->transaction_id }}</small>
                            @endif
                        </div>
                    </div>
                    @elseif($withdraw->status === 'rejected')
                    <div class="timeline-item">
                        <div class="timeline-dot bg-label-danger"><i class="ti tabler-x"></i></div>
                        <div>
                            <strong class="small">Rejected</strong>
                            <div class="text-muted small">{{ $withdraw->processed_at ? $withdraw->processed_at->format('d M Y, h:i A') : $withdraw->updated_at->format('d M Y, h:i A') }}</div>
                            @if($withdraw->admin_note)
                            <small class="text-muted">{{ $withdraw->admin_note }}</small>
                            @endif
                        </div>
                    </div>
                    @elseif($withdraw->status === 'cancelled')
                    <div class="timeline-item">
                        <div class="timeline-dot bg-label-secondary"><i class="ti tabler-ban"></i></div>
                        <div>
                            <strong class="small">Cancelled by Seller</strong>
                            <div class="text-muted small">{{ $withdraw->updated_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    @elseif($withdraw->status === 'pending')
                    <div class="timeline-item">
                        <div class="timeline-dot bg-label-warning"><i class="ti tabler-clock"></i></div>
                        <div>
                            <strong class="small">Awaiting Review</strong>
                            <div class="text-muted small">Pending admin action</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            @if($withdraw->seller)
            <div class="card mb-4">
                <div class="card-header pb-3">
                    <h6 class="mb-0"><i class="ti tabler-bolt me-2 text-primary"></i>Quick Links</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('sellers.show', $withdraw->seller_id) }}" class="btn btn-label-primary btn-sm w-100 mb-2">
                        <i class="ti tabler-building-store me-1"></i> View Seller Profile
                    </a>
                    <a href="{{ route('seller-withdraws.index') }}?status=pending" class="btn btn-label-warning btn-sm w-100">
                        <i class="ti tabler-clock me-1"></i> View All Pending
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>

@endsection

@push('page-js')
<script>
(function($) {
'use strict';

$(document).ready(function() {

    // Copy button
    $(document).on('click', '.copy-btn', function() {
        const val = $(this).data('value');
        navigator.clipboard.writeText(val).then(() => {
            const $el = $(this);
            $el.removeClass('tabler-copy').addClass('tabler-check text-success');
            setTimeout(() => $el.removeClass('tabler-check text-success').addClass('tabler-copy'), 1500);
        });
    });

    @if($withdraw->status === 'pending')
    // Approve
    $('#btn-approve').on('click', function() {
        const txnId = $('#approve-txn-id').val();
        const note = $('#approve-note').val();

        Swal.fire({
            title: 'Approve this withdrawal?',
            html: `<p>Amount: <strong>{{ format_currency($withdraw->amount) }}</strong></p>
                   <p class="text-muted small">The seller's balance will be deducted and this will be marked as paid.</p>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve & Pay',
            customClass: { confirmButton: 'btn btn-success me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

            $.post('{{ route("seller-withdraws.approve", $withdraw->id) }}', {
                _token: '{{ csrf_token() }}',
                transaction_id: txnId,
                admin_note: note
            }).done(r => {
                Swal.fire({ icon: 'success', title: 'Approved!', text: r.message || 'Withdrawal approved successfully.', timer: 2000, showConfirmButton: false, timerProgressBar: true })
                    .then(() => location.reload());
            }).fail(xhr => {
                $btn.prop('disabled', false).html('<i class="ti tabler-check me-1"></i> Approve & Mark as Paid');
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to approve', 'error');
            });
        });
    });

    // Reject
    $('#btn-reject').on('click', function() {
        const reason = $('#reject-reason').val();
        if (!reason.trim()) {
            Swal.fire({ icon: 'warning', title: 'Reason Required', text: 'Please enter a rejection reason.', timer: 2000, showConfirmButton: false });
            return;
        }

        Swal.fire({
            title: 'Reject this withdrawal?',
            html: `<p>Amount: <strong>{{ format_currency($withdraw->amount) }}</strong></p>
                   <p class="text-muted small">Funds will be returned to the seller's available balance.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
            buttonsStyling: false
        }).then(res => {
            if (!res.isConfirmed) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

            $.post('{{ route("seller-withdraws.reject", $withdraw->id) }}', {
                _token: '{{ csrf_token() }}',
                note: reason,
                admin_note: reason
            }).done(r => {
                Swal.fire({ icon: 'success', title: 'Rejected', text: r.message || 'Withdrawal rejected and funds returned.', timer: 2000, showConfirmButton: false, timerProgressBar: true })
                    .then(() => location.reload());
            }).fail(xhr => {
                $btn.prop('disabled', false).html('<i class="ti tabler-x me-1"></i> Reject & Return Funds');
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to reject', 'error');
            });
        });
    });
    @endif
});

})(jQuery);
</script>
@endpush
