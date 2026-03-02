@extends('layouts.app')
@section('title', 'Affiliate Tiers')

@push('page-css')
<style>
    .tier-card { transition: transform .15s ease, box-shadow .15s ease; }
    .tier-card:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,0,0,.08); }
    .tier-default-badge { position:absolute; top:10px; right:10px; }
</style>
@endpush

@section('content')

    @include('partials.alerts')

    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1"><i class="ti tabler-star me-2"></i>Affiliate Tiers</h4>
            <p class="text-muted mb-0">Configure affiliate commission tiers and requirements</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tierModal" id="btn-add-tier">
            <i class="ti tabler-plus me-1"></i> Add Tier
        </button>
    </div>

    {{-- Tier Cards --}}
    <div class="row g-4 mb-4">
        @forelse($tiers as $tier)
        <div class="col-xl-4 col-md-6">
            <div class="card tier-card h-100 position-relative">
                @if($tier->is_default)
                    <span class="tier-default-badge badge bg-primary"><i class="ti tabler-star-filled me-1" style="font-size:.6rem"></i>Default</span>
                @endif
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md me-3 bg-label-{{ $tier->color ?? 'primary' }}">
                            <i class="ti tabler-award fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $tier->name }}</h5>
                            <small class="text-muted">Sort Order: {{ $tier->sort_order }}</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.82rem">
                            <span class="text-muted">L1 Commission</span>
                            <span class="fw-semibold text-success">{{ $tier->commission_rate }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size:.82rem">
                            <span class="text-muted">L2 Commission</span>
                            <span class="fw-semibold text-info">{{ $tier->l2_commission_rate }}%</span>
                        </div>
                    </div>

                    <div class="mb-3 p-2 rounded" style="background:rgba(0,0,0,.02)">
                        <div class="d-flex justify-content-between mb-1" style="font-size:.75rem">
                            <span class="text-muted">Min Earnings</span>
                            <span>${{ number_format($tier->min_earnings_threshold, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1" style="font-size:.75rem">
                            <span class="text-muted">Min Referrals</span>
                            <span>{{ $tier->min_referrals }}</span>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size:.75rem">
                            <span class="text-muted">Min Conversions</span>
                            <span>{{ $tier->min_conversions }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-label-primary flex-fill btn-edit-tier"
                                data-id="{{ $tier->id }}"
                                data-name="{{ $tier->name }}"
                                data-commission-rate="{{ $tier->commission_rate }}"
                                data-l2-commission-rate="{{ $tier->l2_commission_rate }}"
                                data-min-earnings="{{ $tier->min_earnings_threshold }}"
                                data-min-referrals="{{ $tier->min_referrals }}"
                                data-min-conversions="{{ $tier->min_conversions }}"
                                data-color="{{ $tier->color }}"
                                data-sort-order="{{ $tier->sort_order }}"
                                data-is-default="{{ $tier->is_default ? '1' : '0' }}">
                            <i class="ti tabler-edit me-1"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-label-danger btn-delete-tier" data-id="{{ $tier->id }}" data-name="{{ $tier->name }}">
                            <i class="ti tabler-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ti tabler-star-off d-block mb-2" style="font-size:2.5rem;color:#a1acb8"></i>
                    <h6 class="text-muted mb-1">No Tiers Configured</h6>
                    <p class="text-muted mb-3" style="font-size:.85rem">Create your first affiliate tier to get started.</p>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tierModal">
                        <i class="ti tabler-plus me-1"></i> Add Tier
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Tiers Table (Alternative view) --}}
    @if($tiers->count())
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti tabler-table me-2"></i>Tiers Overview</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th class="text-center">L1 Rate</th>
                        <th class="text-center">L2 Rate</th>
                        <th class="text-end">Min Earnings</th>
                        <th class="text-center">Min Referrals</th>
                        <th class="text-center">Min Conversions</th>
                        <th class="text-center">Default</th>
                        <th class="text-center">Order</th>
                        <th class="text-center" width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tiers as $tier)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-xs me-2 bg-label-{{ $tier->color ?? 'primary' }}">
                                    <i class="ti tabler-award" style="font-size:.7rem"></i>
                                </span>
                                <span class="fw-semibold">{{ $tier->name }}</span>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-label-success">{{ $tier->commission_rate }}%</span></td>
                        <td class="text-center"><span class="badge bg-label-info">{{ $tier->l2_commission_rate }}%</span></td>
                        <td class="text-end">${{ number_format($tier->min_earnings_threshold, 2) }}</td>
                        <td class="text-center">{{ $tier->min_referrals }}</td>
                        <td class="text-center">{{ $tier->min_conversions }}</td>
                        <td class="text-center">
                            @if($tier->is_default)
                                <i class="ti tabler-circle-check text-success"></i>
                            @else
                                <i class="ti tabler-circle-x text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center">{{ $tier->sort_order }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-icon btn-label-primary btn-edit-tier"
                                        data-id="{{ $tier->id }}"
                                        data-name="{{ $tier->name }}"
                                        data-commission-rate="{{ $tier->commission_rate }}"
                                        data-l2-commission-rate="{{ $tier->l2_commission_rate }}"
                                        data-min-earnings="{{ $tier->min_earnings_threshold }}"
                                        data-min-referrals="{{ $tier->min_referrals }}"
                                        data-min-conversions="{{ $tier->min_conversions }}"
                                        data-color="{{ $tier->color }}"
                                        data-sort-order="{{ $tier->sort_order }}"
                                        data-is-default="{{ $tier->is_default ? '1' : '0' }}"
                                        data-bs-toggle="tooltip" title="Edit">
                                    <i class="ti tabler-edit" style="font-size:.85rem"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-label-danger btn-delete-tier" data-id="{{ $tier->id }}" data-name="{{ $tier->name }}" data-bs-toggle="tooltip" title="Delete">
                                    <i class="ti tabler-trash" style="font-size:.85rem"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Add/Edit Tier Modal --}}
    <div class="modal fade" id="tierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tierModalTitle"><i class="ti tabler-plus me-2 text-primary"></i>Add Tier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tier-form">
                    <div class="modal-body">
                        <input type="hidden" id="tier-id">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="tier-name" name="name" required placeholder="e.g. Gold Partner">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <select class="form-select" id="tier-color" name="color">
                                    <option value="primary">Primary</option>
                                    <option value="success">Success</option>
                                    <option value="warning">Warning</option>
                                    <option value="danger">Danger</option>
                                    <option value="info">Info</option>
                                    <option value="secondary">Secondary</option>
                                    <option value="dark">Dark</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">L1 Commission Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tier-commission-rate" name="commission_rate" required step="0.01" min="0" max="100" placeholder="e.g. 10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">L2 Commission Rate (%)</label>
                                <input type="number" class="form-control" id="tier-l2-commission-rate" name="l2_commission_rate" step="0.01" min="0" max="100" placeholder="e.g. 5" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Earnings ($)</label>
                                <input type="number" class="form-control" id="tier-min-earnings" name="min_earnings_threshold" step="0.01" min="0" placeholder="0.00" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Referrals</label>
                                <input type="number" class="form-control" id="tier-min-referrals" name="min_referrals" min="0" placeholder="0" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Conversions</label>
                                <input type="number" class="form-control" id="tier-min-conversions" name="min_conversions" min="0" placeholder="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" id="tier-sort-order" name="sort_order" min="0" placeholder="0" value="0">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tier-is-default" name="is_default" value="1">
                                    <label class="form-check-label" for="tier-is-default">Set as Default Tier</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-save-tier">
                            <i class="ti tabler-check me-1"></i> <span id="btn-save-text">Save Tier</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
<script>
(function($) {
'use strict';

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

const TiersPage = {
    modal: null,

    init() {
        this.modal = new bootstrap.Modal('#tierModal');
        this.bindEvents();
    },

    bindEvents() {
        const self = this;

        $('#btn-add-tier').on('click', () => self.resetForm());

        $(document).on('click', '.btn-edit-tier', function() {
            const $btn = $(this);
            self.resetForm();
            $('#tierModalTitle').html('<i class="ti tabler-edit me-2 text-primary"></i>Edit Tier');
            $('#btn-save-text').text('Update Tier');
            $('#tier-id').val($btn.data('id'));
            $('#tier-name').val($btn.data('name'));
            $('#tier-commission-rate').val($btn.data('commission-rate'));
            $('#tier-l2-commission-rate').val($btn.data('l2-commission-rate'));
            $('#tier-min-earnings').val($btn.data('min-earnings'));
            $('#tier-min-referrals').val($btn.data('min-referrals'));
            $('#tier-min-conversions').val($btn.data('min-conversions'));
            $('#tier-color').val($btn.data('color'));
            $('#tier-sort-order').val($btn.data('sort-order'));
            $('#tier-is-default').prop('checked', $btn.data('is-default') == '1');
            self.modal.show();
        });

        $('#tier-form').on('submit', function(e) {
            e.preventDefault();
            self.saveTier();
        });

        $(document).on('click', '.btn-delete-tier', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            Swal.fire({
                title: `Delete "${name}" tier?`,
                text: 'Affiliates on this tier will need to be reassigned.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(res => {
                if (!res.isConfirmed) return;
                $.ajax({
                    url: self.tierUrl(id),
                    type: 'DELETE'
                })
                .done(() => {
                    Swal.fire({ icon: 'success', title: 'Tier deleted', showConfirmButton: false, timer: 1500, timerProgressBar: true })
                        .then(() => location.reload());
                })
                .fail(xhr => Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not delete tier.', timer: 2000, showConfirmButton: false }));
            });
        });
    },

    resetForm() {
        $('#tierModalTitle').html('<i class="ti tabler-plus me-2 text-primary"></i>Add Tier');
        $('#btn-save-text').text('Save Tier');
        $('#tier-id').val('');
        $('#tier-form')[0].reset();
        $('#tier-l2-commission-rate').val('0');
        $('#tier-min-earnings').val('0');
        $('#tier-min-referrals').val('0');
        $('#tier-min-conversions').val('0');
        $('#tier-sort-order').val('0');
    },

    saveTier() {
        const id = $('#tier-id').val();
        const data = {
            name: $('#tier-name').val(),
            commission_rate: $('#tier-commission-rate').val(),
            l2_commission_rate: $('#tier-l2-commission-rate').val(),
            min_earnings_threshold: $('#tier-min-earnings').val(),
            min_referrals: $('#tier-min-referrals').val(),
            min_conversions: $('#tier-min-conversions').val(),
            color: $('#tier-color').val(),
            sort_order: $('#tier-sort-order').val(),
            is_default: $('#tier-is-default').is(':checked') ? 1 : 0
        };

        const isUpdate = !!id;
        const url = isUpdate ? this.tierUrl(id) : '{{ route("affiliate-tiers.store") }}';
        const method = isUpdate ? 'PUT' : 'POST';

        $('#btn-save-tier').prop('disabled', true);

        $.ajax({ url, type: method, data })
            .done(() => {
                this.modal.hide();
                Swal.fire({
                    icon: 'success',
                    title: isUpdate ? 'Tier updated' : 'Tier created',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(() => location.reload());
            })
            .fail(xhr => {
                let msg = xhr.responseJSON?.message || 'Could not save tier.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({ icon: 'error', title: 'Validation Error', html: msg, showConfirmButton: true });
            })
            .always(() => $('#btn-save-tier').prop('disabled', false));
    },

    tierUrl(id) {
        return '{{ route("affiliate-tiers.update", ":id") }}'.replace(':id', id);
    }
};

$(document).ready(() => TiersPage.init());

})(jQuery);
</script>
@endpush
