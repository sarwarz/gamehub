@extends('layouts.app')
@section('title', 'Affiliate Details — ' . $affiliate->referral_code)

@php
    $statusColors = [
        'pending' => 'warning', 'active' => 'success', 'suspended' => 'danger', 'rejected' => 'secondary',
    ];
    $statusIcons = [
        'pending' => 'tabler-clock', 'active' => 'tabler-circle-check', 'suspended' => 'tabler-ban', 'rejected' => 'tabler-circle-x',
    ];
@endphp

@push('page-css')
<style>
    .stat-card { transition: transform .15s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .sb-row { display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid rgba(0,0,0,.04); font-size:.82rem; }
    .sb-row:last-child { border-bottom:0; }
    .sb-lbl { color:#a1acb8; font-size:.75rem; }
    .sb-val { font-weight:500; }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
        <div>
            <a href="{{ route('affiliates.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block" style="font-size:.82rem">
                <i class="ti tabler-arrow-left me-1"></i>Back to Affiliates
            </a>
            <h4 class="mb-1">
                <i class="ti tabler-affiliate me-2"></i>Affiliate Details — {{ $affiliate->referral_code }}
                <span class="badge bg-{{ $statusColors[$affiliate->status] ?? 'secondary' }}" style="font-size:.68rem">
                    <i class="ti {{ $statusIcons[$affiliate->status] ?? 'tabler-circle' }} me-1" style="font-size:.7rem"></i>{{ ucfirst($affiliate->status) }}
                </span>
            </h4>
            <p class="text-muted mb-0">{{ $affiliate->user->name ?? 'Unknown User' }} &mdash; {{ $affiliate->user->email ?? '' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($affiliate->status === 'pending')
                <button class="btn btn-success btn-sm btn-action" data-action="approve" data-id="{{ $affiliate->id }}">
                    <i class="ti tabler-circle-check me-1"></i> Approve
                </button>
            @endif
            @if($affiliate->status === 'active')
                <button class="btn btn-warning btn-sm btn-action" data-action="suspend" data-id="{{ $affiliate->id }}">
                    <i class="ti tabler-ban me-1"></i> Suspend
                </button>
            @endif
            @if($affiliate->status === 'suspended')
                <button class="btn btn-success btn-sm btn-action" data-action="reactivate" data-id="{{ $affiliate->id }}">
                    <i class="ti tabler-refresh me-1"></i> Reactivate
                </button>
            @endif
        </div>
    </div>

    {{-- Main Layout --}}
    <div class="row g-4">
        {{-- LEFT: Main Content --}}
        <div class="col-lg-8">

            {{-- Affiliate Info Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-info-circle me-2 text-primary"></i>Affiliate Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="sb-row"><span class="sb-lbl">User</span><span class="sb-val">{{ $affiliate->user->name ?? '—' }}</span></div>
                            <div class="sb-row"><span class="sb-lbl">Email</span><span class="sb-val">{{ $affiliate->user->email ?? '—' }}</span></div>
                            <div class="sb-row"><span class="sb-lbl">Referral Code</span><span class="sb-val"><code>{{ $affiliate->referral_code }}</code></span></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="sb-row">
                                <span class="sb-lbl">Tier</span>
                                <span class="sb-val">
                                    @if($affiliate->tier)
                                        <span class="badge bg-label-{{ $affiliate->tier->color ?? 'primary' }}">{{ $affiliate->tier->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </span>
                            </div>
                            <div class="sb-row">
                                <span class="sb-lbl">Status</span>
                                <span class="sb-val">
                                    <span class="badge bg-label-{{ $statusColors[$affiliate->status] ?? 'secondary' }}">
                                        <i class="ti {{ $statusIcons[$affiliate->status] ?? 'tabler-circle' }} me-1" style="font-size:.65rem"></i>{{ ucfirst($affiliate->status) }}
                                    </span>
                                </span>
                            </div>
                            <div class="sb-row"><span class="sb-lbl">Joined</span><span class="sb-val">{{ $affiliate->created_at->format('M d, Y H:i') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Commissions --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="ti tabler-coins me-2 text-success"></i>Recent Commissions</h6>
                    <a href="{{ route('affiliate-commissions.index', ['affiliate' => $affiliate->id]) }}" class="btn btn-sm btn-label-primary">
                        <i class="ti tabler-external-link me-1"></i> View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Level</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCommissions as $commission)
                            <tr>
                                <td>
                                    @if($commission->order)
                                        <a href="{{ route('orders.show', $commission->order_id) }}" class="fw-semibold text-primary">#{{ $commission->order->order_number }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">${{ number_format($commission->amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $commission->level == 1 ? 'primary' : 'info' }}">L{{ $commission->level }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $cColors = ['pending'=>'warning','held'=>'info','available'=>'success','paid'=>'primary','reversed'=>'danger'];
                                    @endphp
                                    <span class="badge bg-label-{{ $cColors[$commission->status] ?? 'secondary' }}">{{ ucfirst($commission->status) }}</span>
                                </td>
                                <td>{{ $commission->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="ti tabler-cash-off d-block mb-2" style="font-size:1.5rem"></i>No commissions yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Referrals --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-users me-2 text-info"></i>Recent Referrals</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Converted</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReferrals as $referral)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($referral->referred->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-semibold d-block" style="font-size:.82rem">{{ $referral->referred->name ?? '—' }}</span>
                                            <small class="text-muted">{{ $referral->referred->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $referral->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($referral->status) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($referral->converted_at)
                                        <i class="ti tabler-circle-check text-success"></i>
                                    @else
                                        <i class="ti tabler-circle-x text-muted"></i>
                                    @endif
                                </td>
                                <td>{{ $referral->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="ti tabler-users-minus d-block mb-2" style="font-size:1.5rem"></i>No referrals yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="col-lg-4">

            {{-- Balance Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-wallet me-2 text-success"></i>Balance</h6>
                </div>
                <div class="card-body">
                    <div class="sb-row">
                        <span class="sb-lbl">Available</span>
                        <span class="sb-val text-success">${{ number_format($affiliate->available_balance, 2) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Pending</span>
                        <span class="sb-val text-warning">${{ number_format($affiliate->pending_balance, 2) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Total Earned</span>
                        <span class="sb-val text-primary">${{ number_format($affiliate->total_earned, 2) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Total Paid</span>
                        <span class="sb-val">${{ number_format($affiliate->total_paid, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Commission Stats Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-chart-bar me-2 text-primary"></i>Commission Stats</h6>
                </div>
                <div class="card-body">
                    <div class="sb-row">
                        <span class="sb-lbl">Total Commissions</span>
                        <span class="sb-val">{{ $commissionStats['total'] ?? 0 }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">This Month</span>
                        <span class="sb-val">${{ number_format($commissionStats['this_month'] ?? 0, 2) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Last Month</span>
                        <span class="sb-val">${{ number_format($commissionStats['last_month'] ?? 0, 2) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Avg. Commission</span>
                        <span class="sb-val">${{ number_format($commissionStats['average'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Referral Stats Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-click me-2 text-info"></i>Referral Stats</h6>
                </div>
                <div class="card-body">
                    <div class="sb-row">
                        <span class="sb-lbl">Total Clicks</span>
                        <span class="sb-val">{{ number_format($referralStats['clicks'] ?? 0) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Registrations</span>
                        <span class="sb-val">{{ number_format($referralStats['registrations'] ?? 0) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Conversions</span>
                        <span class="sb-val text-success">{{ number_format($referralStats['conversions'] ?? 0) }}</span>
                    </div>
                    <div class="sb-row">
                        <span class="sb-lbl">Conversion Rate</span>
                        <span class="sb-val">
                            <span class="badge bg-label-{{ ($referralStats['conversion_rate'] ?? 0) >= 10 ? 'success' : (($referralStats['conversion_rate'] ?? 0) >= 5 ? 'warning' : 'secondary') }}">
                                {{ number_format($referralStats['conversion_rate'] ?? 0, 1) }}%
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti tabler-adjustments-horizontal me-2 text-primary"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($affiliate->status === 'pending')
                            <button class="btn btn-success btn-sm flex-fill btn-action" data-action="approve" data-id="{{ $affiliate->id }}">
                                <i class="ti tabler-circle-check me-1"></i> Approve
                            </button>
                        @endif
                        @if($affiliate->status === 'active')
                            <button class="btn btn-warning btn-sm flex-fill btn-action" data-action="suspend" data-id="{{ $affiliate->id }}">
                                <i class="ti tabler-ban me-1"></i> Suspend
                            </button>
                        @endif
                        @if($affiliate->status === 'suspended')
                            <button class="btn btn-success btn-sm flex-fill btn-action" data-action="reactivate" data-id="{{ $affiliate->id }}">
                                <i class="ti tabler-refresh me-1"></i> Reactivate
                            </button>
                        @endif
                    </div>

                    <label class="form-label small text-muted">Change Tier</label>
                    <div class="d-flex gap-1">
                        <select class="form-select form-select-sm" id="change-tier" style="flex:1">
                            @foreach($tiers as $tier)
                                <option value="{{ $tier->id }}" {{ $affiliate->affiliate_tier_id == $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-icon btn-primary" id="btn-change-tier" data-bs-toggle="tooltip" title="Save">
                            <i class="ti tabler-check" style="font-size:.8rem"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('page-js')
<script>
(function($) {
'use strict';

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const affiliateId = {{ $affiliate->id }};

$(document).on('click', '.btn-action', function() {
    const action = $(this).data('action');
    const id = $(this).data('id');
    const labels = { approve: 'Approve', suspend: 'Suspend', reactivate: 'Reactivate' };
    const icons  = { approve: 'question', suspend: 'warning', reactivate: 'question' };
    const colors = { approve: 'btn btn-success me-3', suspend: 'btn btn-warning me-3', reactivate: 'btn btn-success me-3' };

    Swal.fire({
        title: `${labels[action]} this affiliate?`,
        icon: icons[action],
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}`,
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: colors[action], cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then(res => {
        if (!res.isConfirmed) return;
        const url = '{{ url("dashboard/affiliates") }}/' + id + '/' + action;
        $.post(url)
            .done(() => {
                Swal.fire({ icon: 'success', title: `Affiliate ${action}d`, showConfirmButton: false, timer: 1500, timerProgressBar: true })
                    .then(() => location.reload());
            })
            .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || `Could not ${action} affiliate.`, timer: 2000, showConfirmButton: false }));
    });
});

$('#btn-change-tier').on('click', function() {
    const tierId = $('#change-tier').val();
    $.ajax({
        url: '{{ route("affiliates.update", $affiliate->id) }}',
        type: 'PUT',
        data: { affiliate_tier_id: tierId }
    })
    .done(() => {
        Swal.fire({ icon: 'success', title: 'Tier updated', showConfirmButton: false, timer: 1500, timerProgressBar: true })
            .then(() => location.reload());
    })
    .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not update tier.', timer: 2000, showConfirmButton: false }));
});

})(jQuery);
</script>
@endpush
