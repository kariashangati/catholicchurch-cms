@extends('layouts.admin')

@section('title', $pageTitle)

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 position-relative align-items-center">
            <div class="col-lg-8">
                <span class="ui-page-badge">{{ db_trans('bulk_tithe_entry') }}</span>
                <h1 class="ui-page-title">{{ db_trans('bulk_tithe_entry') }}</h1>
                <p class="ui-page-subtitle">{{ db_trans('select_kanda_jumuiya_and_posting_date_first') }}</p>
            </div>

            <div class="col-lg-4">
                <div class="ui-meta-wrap">
                    <span class="ui-meta-pill">
                        <i class="fas fa-calendar"></i>{{ $selectedMonthLabel }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('date') }}</label>
                    <input type="date" class="form-control" name="date" value="{{ $selectedDate }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select class="form-select" name="kanda_id" id="bulkKandaSelect">
                        <option value="">{{ db_trans('select_kanda') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string) $selectedKandaId === (string) $kanda->id)>
                                {{ $kanda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select class="form-select" name="jumuiya_id" id="bulkJumuiyaSelect">
                        <option value="">{{ db_trans('select_jumuiya') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option
                                value="{{ $jumuiya->id }}"
                                data-kanda="{{ $jumuiya->kanda_id }}"
                                @selected((string) $selectedJumuiyaId === (string) $jumuiya->id)>
                                {{ $jumuiya->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn ui-btn-primary">
                        <i class="fas fa-search me-2"></i>{{ db_trans('load') }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($selectedJumuiyaId)
        <form method="POST" action="{{ route('finance.tithes.bulk.store') }}" id="bulkTitheForm">
            @csrf

            <input type="hidden" name="kanda_id" value="{{ $selectedKandaId }}">
            <input type="hidden" name="jumuiya_id" value="{{ $selectedJumuiyaId }}">
            <input type="hidden" name="contribution_date" value="{{ $selectedDate }}">
            <input type="hidden" name="expected_total" id="expectedTotalInput" value="0">
            <input type="hidden" name="open_denomination_modal" id="openDenominationModal" value="0">

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card ui-table-card border-0">
                        <div class="card-body p-4">
                            <div class="ui-section-heading">
                                <div>
                                    <h5 class="ui-section-title">{{ db_trans('members') }}</h5>
                                    <p class="ui-section-subtitle">{{ db_trans('rows_already_recorded_this_month_are_marked') }}</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="bulkRosterTable">
                                    <thead>
                                        <tr>
                                            <th>{{ db_trans('member') }}</th>
                                            <th>{{ db_trans('phone') }}</th>
                                            <th>{{ db_trans('bahasha') }}</th>
                                            <th>{{ db_trans('last_record') }}</th>
                                            <th>{{ db_trans('amount') }}</th>
                                            <th>{{ db_trans('notes') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $index => $member)
                                            @php
                                                $existing = collect($existingMembers->get($member->id, collect()));
                                                $latest = $existing->sortByDesc('contribution_date')->sortByDesc('id')->first();
                                            @endphp

                                            <tr class="{{ $existing->isNotEmpty() ? 'row-has-existing' : '' }}">
                                                <td>
                                                    <strong>{{ $member->full_name }}</strong>
                                                    <div class="text-muted small">
                                                        {{ $member->member_code ?: '—' }} · {{ $member->familia?->name ?? '—' }}
                                                    </div>
                                                    <input type="hidden" name="rows[{{ $index }}][member_id]" value="{{ $member->id }}">
                                                </td>

                                                <td>{{ $member->phone ?: '—' }}</td>
                                                <td>{{ $member->bahasha ?: '—' }}</td>

                                                <td>
                                                    @if($latest)
                                                        <span class="ui-status-pill ui-status-warning">
                                                            {{ $existing->count() }} {{ db_trans('records') }}
                                                        </span>
                                                        <div class="small mt-1">
                                                            {{ optional($latest->contribution_date)->format('d/m/Y') }}
                                                            · {{ number_format((float) $latest->amount, 2) }}
                                                            · {{ $latest->recorder?->name ?? '—' }}
                                                        </div>
                                                    @else
                                                        <span class="ui-status-pill">{{ db_trans('none') }}</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="form-control tithe-amount-input"
                                                        name="rows[{{ $index }}][amount]"
                                                        value="{{ old("rows.$index.amount") }}">
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="rows[{{ $index }}][notes]"
                                                        value="{{ old("rows.$index.notes") }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card ui-side-card border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="ui-section-title">{{ db_trans('override_control') }}</h5>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" value="1" id="overrideExisting" name="override_existing">
                                <label class="form-check-label" for="overrideExisting">
                                    {{ db_trans('allow_same_member_within_same_month_only_through_override') }}
                                </label>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">{{ db_trans('override_reason') }}</label>
                                <textarea class="form-control" name="override_reason" rows="4">{{ old('override_reason') }}</textarea>
                            </div>

                            <button type="button" class="btn ui-btn-primary w-100 mt-4" id="openDenominationStep">
                                {{ db_trans('continue_to_denomination_review') }}
                            </button>
                        </div>
                    </div>

                    <div class="card ui-side-card border-0">
                        <div class="card-body p-4">
                            <h5 class="ui-section-title">{{ db_trans('totals_preview') }}</h5>

                            <div class="ui-note-box mt-3">
                                <div class="ui-note-title">{{ db_trans('totals') }}</div>
                                <div class="small">{{ db_trans('tithe_total') }}: <strong id="titheTotalText">0.00</strong></div>
                                <div class="small">{{ db_trans('denomination_total') }}: <strong id="denominationTotalText">0.00</strong></div>
                                <div class="small">{{ db_trans('variance') }}: <strong id="varianceText">0.00</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="denominationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content ui-modal-card admin-ui-v4">
                        <div class="modal-header ui-modal-header">
                            <h5 class="ui-modal-title">{{ db_trans('denomination_review') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="ui-section-subtitle mb-3">{{ db_trans('realtime_total_must_match_tithe_total') }}</p>

                            @foreach($denominationOptions as $i => $value)
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-5">
                                        <input type="hidden" name="denominations[{{ $i }}][value]" value="{{ $value }}">
                                        <input class="form-control" value="{{ number_format($value) }}" disabled>
                                    </div>
                                    <div class="col-7">
                                        <input
                                            type="number"
                                            min="0"
                                            class="form-control denomination-qty"
                                            name="denominations[{{ $i }}][quantity]"
                                            data-value="{{ $value }}"
                                            value="0">
                                    </div>
                                </div>
                            @endforeach

                            <div class="ui-note-box mt-3">
                                <div class="ui-note-title">{{ db_trans('totals') }}</div>
                                <div class="small">{{ db_trans('tithe_total') }}: <strong id="modalTitheTotalText">0.00</strong></div>
                                <div class="small">{{ db_trans('denomination_total') }}: <strong id="modalDenominationTotalText">0.00</strong></div>
                                <div class="small">{{ db_trans('variance') }}: <strong id="modalVarianceText">0.00</strong></div>
                            </div>

                            <div class="ui-note-box mt-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" value="1" id="bulkSendSms" name="send_sms">
                                    <label class="form-check-label fw-semibold" for="bulkSendSms">
                                        {{ db_trans('send_sms_acknowledgement') }}
                                    </label>
                                </div>
                                <div class="small text-muted mt-2">
                                    {{ db_trans('bulk_sms_acknowledgement_hint') }}
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer ui-modal-footer">
                            <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">
                                {{ db_trans('cancel') }}
                            </button>
                            <button type="button" class="btn ui-btn-primary" id="confirmBulkSave">
                                {{ db_trans('confirm_and_save') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kandaSelect = document.getElementById('bulkKandaSelect');
    const jumuiyaSelect = document.getElementById('bulkJumuiyaSelect');

    function syncJumuiyas() {
        if (!kandaSelect || !jumuiyaSelect) {
            return;
        }

        const kanda = kandaSelect.value;

        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !kanda || option.dataset.kanda !== kanda;
        });

        if (jumuiyaSelect.selectedOptions[0] && jumuiyaSelect.selectedOptions[0].hidden) {
            jumuiyaSelect.value = '';
        }
    }

    syncJumuiyas();

    if (kandaSelect) {
        kandaSelect.addEventListener('change', syncJumuiyas);
    }

    if (window.jQuery && document.getElementById('bulkRosterTable')) {
        $('#bulkRosterTable').DataTable({
            pageLength: 25,
            order: [],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, @json(db_trans('all'))]],
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search')) + '...',
                lengthMenu: '_MENU_',
                zeroRecords: @json(db_trans('no_records_found')),
                info: @json(db_trans('showing_records_info')),
                infoEmpty: @json(db_trans('no_records_found')),
                paginate: {
                    previous: @json(db_trans('previous')),
                    next: @json(db_trans('next'))
                }
            }
        });
    }

    function updateTotals() {
        let tithe = 0;
        let denomination = 0;

        document.querySelectorAll('.tithe-amount-input').forEach(function (input) {
            tithe += parseFloat(input.value || 0);
        });

        document.querySelectorAll('.denomination-qty').forEach(function (input) {
            denomination += parseFloat(input.dataset.value || 0) * parseInt(input.value || 0, 10);
        });

        const variance = denomination - tithe;

        ['titheTotalText', 'modalTitheTotalText'].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = tithe.toFixed(2);
            }
        });

        ['denominationTotalText', 'modalDenominationTotalText'].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = denomination.toFixed(2);
            }
        });

        ['varianceText', 'modalVarianceText'].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = variance.toFixed(2);
            }
        });

        const expected = document.getElementById('expectedTotalInput');
        if (expected) {
            expected.value = tithe.toFixed(2);
        }
    }

    document.querySelectorAll('.tithe-amount-input, .denomination-qty').forEach(function (input) {
        input.addEventListener('input', updateTotals);
    });

    updateTotals();

    const openButton = document.getElementById('openDenominationStep');
    const form = document.getElementById('bulkTitheForm');
    const denominationModalElement = document.getElementById('denominationModal');
    const denominationModal = denominationModalElement && window.bootstrap
        ? bootstrap.Modal.getOrCreateInstance(denominationModalElement)
        : null;

    const existingCount = document.querySelectorAll('.row-has-existing').length;

    if (openButton) {
        openButton.addEventListener('click', function () {
            updateTotals();

            const total = parseFloat(document.getElementById('expectedTotalInput')?.value || '0');

            if (total <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: @json(db_trans('please_fix_the_following_errors')),
                    text: @json(db_trans('enter_at_least_one_tithe_amount'))
                });
                return;
            }

            if (existingCount > 0 && !document.getElementById('overrideExisting')?.checked) {
                Swal.fire({
                    icon: 'warning',
                    title: @json(db_trans('monthly_records_already_exist')),
                    text: @json(db_trans('enable_override_and_give_reason_to_continue'))
                });
                return;
            }

            if (existingCount > 0 && !document.querySelector('textarea[name="override_reason"]')?.value.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: @json(db_trans('override_reason')),
                    text: @json(db_trans('override_reason_required'))
                });
                return;
            }

            if (denominationModal) {
                denominationModal.show();
            }
        });
    }

    const confirmButton = document.getElementById('confirmBulkSave');

    if (confirmButton && form) {
        confirmButton.addEventListener('click', function () {
            updateTotals();

            const variance = parseFloat(document.getElementById('modalVarianceText')?.textContent || '0');

            if (Math.abs(variance) > 0.009) {
                Swal.fire({
                    icon: 'error',
                    title: @json(db_trans('totals_do_not_match')),
                    text: @json(db_trans('denomination_total_must_match_tithe_total'))
                });
                return;
            }

            confirmButton.disabled = true;
            confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + @json(db_trans('saving'));

            form.submit();
        });
    }
});
</script>
@endpush