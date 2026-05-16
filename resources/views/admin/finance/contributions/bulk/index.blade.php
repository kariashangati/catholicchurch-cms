@extends('layouts.admin')

@section('title', db_trans('bulk_contributions'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $filters = $filters ?? [];
    $rows = collect($rows ?? []);
    $existingMembers = $existingMembers ?? collect();
    $denominationOptions = $denominationOptions ?? [100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100, 50];
    $hasRoster = !empty($filters['kanda_id']) && !empty($filters['jumuiya_id']) && !empty($filters['contribution_type_id']) && !empty($filters['source_type']) && !empty($filters['contribution_date']);
    $selectedSource = $filters['source_type'] ?? \App\Models\ContributionBatch::SOURCE_CASH;
    $statusOptions = $selectedSource === \App\Models\ContributionBatch::SOURCE_BANK ? ($bankStatuses ?? []) : ($cashStatuses ?? []);
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge"><i class="fas fa-users-line"></i>{{ db_trans('bulk_contributions') }}</span>
                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('bulk_contributions') }}</h1>
                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill"><i class="fas fa-users"></i>{{ number_format($rows->count()) }} {{ db_trans('members') }}</span>
                    <span class="ui-meta-pill"><i class="fas fa-calendar"></i>{{ $filters['contribution_date'] ?? now()->toDateString() }}</span>
                    <span class="ui-meta-pill ui-meta-pill-warning"><i class="fas fa-coins"></i>{{ db_trans($selectedSource) }}</span>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ Route::has('finance.contributions.cash.index') ? route('finance.contributions.cash.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-wallet"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('cash_contributions') }}</span>
                    </a>
                    <a href="{{ Route::has('finance.contributions.bank.index') ? route('finance.contributions.bank.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-university"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('bank_contributions') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('finance.contributions.bulk.index') }}" class="card ui-filter-card border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select name="kanda_id" id="bulkContributionKanda" class="form-select" required>
                        <option value="">{{ db_trans('select_kanda') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string)($filters['kanda_id'] ?? '') === (string)$kanda->id)>{{ $kanda->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                  <select name="jumuiya_id" id="bulkContributionJumuiya" class="form-select" required @disabled(empty($filters['kanda_id']))>
                        <option value="">{{ db_trans('select_jumuiya') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected((string)($filters['jumuiya_id'] ?? '') === (string)$jumuiya->id)>{{ $jumuiya->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('contribution_type') }}</label>
                    <select name="contribution_type_id" class="form-select" required>
                        <option value="">{{ db_trans('select_option') }}</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected((string)($filters['contribution_type_id'] ?? '') === (string)$type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('source') }}</label>
                    <select name="source_type" id="bulkContributionSource" class="form-select" required>
                        @foreach($sourceOptions as $source)
                            <option value="{{ $source }}" @selected(($filters['source_type'] ?? 'cash') === $source)>{{ db_trans($source) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 bulk-bank-field">
                    <label class="form-label">{{ db_trans('bank_account') }}</label>
                    <select name="bank_account_id" class="form-select">
                        <option value="">{{ db_trans('select_option') }}</option>
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}" @selected((string)($filters['bank_account_id'] ?? '') === (string)$account->id)>{{ $account->display_name ?? $account->account_name ?? $account->bank_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label">{{ db_trans('contribution_date') }}</label>
                    <input type="date" name="contribution_date" class="form-control" value="{{ $filters['contribution_date'] ?? now()->toDateString() }}" required>
                </div>
                <div class="col-xl-2 col-md-4">
                    <button class="btn ui-btn-primary w-100"><i class="fas fa-filter me-1"></i>{{ db_trans('load') }}</button>
                </div>
            </div>
        </div>
    </form>

    @if($hasRoster)
    <form method="POST" action="{{ route('finance.contributions.bulk.store') }}" id="bulkContributionForm">
        @csrf
        <input type="hidden" name="kanda_id" value="{{ $filters['kanda_id'] }}">
        <input type="hidden" name="jumuiya_id" value="{{ $filters['jumuiya_id'] }}">
        <input type="hidden" name="contribution_type_id" value="{{ $filters['contribution_type_id'] }}">
        <input type="hidden" name="source_type" value="{{ $filters['source_type'] }}">
        <input type="hidden" name="bank_account_id" value="{{ $filters['bank_account_id'] }}">
        <input type="hidden" name="contribution_date" value="{{ $filters['contribution_date'] }}">
        <input type="hidden" name="expected_total" id="bulkExpectedTotal" value="0">

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="ui-table-card p-4">
                    <div class="ui-section-heading"><h5 class="mb-1">{{ db_trans('members') }}</h5></div>
                    <div class="table-responsive">
                        <table class="table align-middle" id="bulkContributionTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ db_trans('member') }}</th>
                                    <th>{{ db_trans('phone') }}</th>
                                    <th>{{ db_trans('last_record') }}</th>
                                    <th>{{ db_trans('amount') }}</th>
                                    <th>{{ db_trans('notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $member)
                                    @php
                                        $memberExisting = collect($existingMembers->get($member->id, collect()));
                                        $latest = $memberExisting->sortByDesc('contribution_date')->sortByDesc('id')->first();
                                    @endphp
                                    <tr class="{{ $memberExisting->isNotEmpty() ? 'row-has-existing' : '' }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $member->full_name ?? trim($member->first_name . ' ' . $member->last_name) }}</strong>
                                            <div class="small text-muted">{{ $member->member_code }} · {{ $member->familia?->name }}</div>
                                            <input type="hidden" name="rows[{{ $loop->index }}][member_id]" value="{{ $member->id }}">
                                        </td>
                                        <td>{{ $member->phone ?: '—' }}</td>
                                        <td>
                                            @if($latest)
                                                <span class="ui-status-pill ui-status-warning">{{ $memberExisting->count() }} {{ db_trans('records') }}</span>
                                                <div class="small mt-1">{{ optional($latest->contribution_date)->format('d/m/Y') }} · {{ number_format((float)$latest->amount, 2) }}</div>
                                            @else
                                                <span class="ui-status-pill">{{ db_trans('none') }}</span>
                                            @endif
                                        </td>
                                        <td><input type="number" step="0.01" min="0" class="form-control bulk-contribution-amount" name="rows[{{ $loop->index }}][amount]"></td>
                                        <td><input type="text" class="form-control" name="rows[{{ $loop->index }}][notes]"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="ui-side-card p-4 mb-4">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select mb-3">
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}">{{ db_trans($status) }}</option>
                        @endforeach
                    </select>
                    <label class="form-label">{{ db_trans('notes') }}</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                    <button type="button" id="openContributionDenomination" class="btn ui-btn-primary w-100 mt-4">{{ db_trans('continue_to_denomination_review') }}</button>
                </div>
                <div class="ui-side-card p-4">
                    <h5 class="mb-3">{{ db_trans('totals') }}</h5>
                    <div>{{ db_trans('contribution_total') }}: <strong id="bulkContributionTotalText">0.00</strong></div>
                    <div>{{ db_trans('denomination_total') }}: <strong id="bulkDenominationTotalText">0.00</strong></div>
                    <div>{{ db_trans('variance') }}: <strong id="bulkVarianceText">0.00</strong></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="contributionDenominationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content ui-modal-card admin-ui-v4">
                    <div class="modal-header"><h5 class="modal-title">{{ db_trans('denomination_review') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @foreach($denominationOptions as $i => $value)
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-5"><input type="hidden" name="denominations[{{ $i }}][value]" value="{{ $value }}"><input class="form-control" value="{{ number_format($value) }}" disabled></div>
                                <div class="col-7"><input type="number" min="0" class="form-control bulk-denomination-qty" name="denominations[{{ $i }}][quantity]" data-value="{{ $value }}" value="0"></div>
                            </div>
                        @endforeach
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" value="1" name="send_sms" id="bulkSendSms">
                            <label class="form-check-label" for="bulkSendSms">{{ db_trans('send_sms_acknowledgement') }}</label>
                        </div>
                        <div class="ui-note-box mt-3">
                            <div>{{ db_trans('contribution_total') }}: <strong id="modalBulkContributionTotalText">0.00</strong></div>
                            <div>{{ db_trans('denomination_total') }}: <strong id="modalBulkDenominationTotalText">0.00</strong></div>
                            <div>{{ db_trans('variance') }}: <strong id="modalBulkVarianceText">0.00</strong></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="button" class="btn ui-btn-primary" id="confirmBulkContributionSave">{{ db_trans('confirm_and_save') }}</button></div>
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
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kandaSelect = document.getElementById('bulkContributionKanda');
    const jumuiyaSelect = document.getElementById('bulkContributionJumuiya');
    const sourceSelect = document.getElementById('bulkContributionSource');
    const bankFields = document.querySelectorAll('.bulk-bank-field');

const syncJumuiyas = function () {
    if (!kandaSelect || !jumuiyaSelect) return;

    const kanda = kandaSelect.value;

    Array.from(jumuiyaSelect.options).forEach(function (option) {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        option.hidden = !kanda || option.dataset.kanda !== kanda;
    });

    const selected = jumuiyaSelect.selectedOptions[0];

    if (!kanda || (selected && selected.hidden)) {
        jumuiyaSelect.value = '';
    }

    jumuiyaSelect.disabled = !kanda;
};

    const syncBankFields = function () {
        const isBank = sourceSelect && sourceSelect.value === 'bank';
        bankFields.forEach(function (field) { field.style.display = isBank ? '' : 'none'; });
    };

    syncJumuiyas(); syncBankFields();
    if (kandaSelect) kandaSelect.addEventListener('change', syncJumuiyas);
    if (sourceSelect) sourceSelect.addEventListener('change', syncBankFields);

    if (window.jQuery && $.fn.DataTable && $('#bulkContributionTable').length) {
        $('#bulkContributionTable').DataTable({ responsive: true, pageLength: 25, order: [] });
    }

    const updateTotals = function () {
        let total = 0, denom = 0;
        document.querySelectorAll('.bulk-contribution-amount').forEach(function (el) { total += parseFloat(el.value || 0); });
        document.querySelectorAll('.bulk-denomination-qty').forEach(function (el) { denom += parseFloat(el.dataset.value || 0) * parseInt(el.value || 0, 10); });
        const variance = denom - total;
        ['bulkContributionTotalText','modalBulkContributionTotalText'].forEach(function (id) { const el = document.getElementById(id); if (el) el.textContent = total.toFixed(2); });
        ['bulkDenominationTotalText','modalBulkDenominationTotalText'].forEach(function (id) { const el = document.getElementById(id); if (el) el.textContent = denom.toFixed(2); });
        ['bulkVarianceText','modalBulkVarianceText'].forEach(function (id) { const el = document.getElementById(id); if (el) el.textContent = variance.toFixed(2); });
        const expected = document.getElementById('bulkExpectedTotal'); if (expected) expected.value = total.toFixed(2);
    };

    document.querySelectorAll('.bulk-contribution-amount,.bulk-denomination-qty').forEach(function (el) { el.addEventListener('input', updateTotals); });
    updateTotals();

    const modalEl = document.getElementById('contributionDenominationModal');
    const modal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('bulkContributionForm');

    const openBtn = document.getElementById('openContributionDenomination');
    if (openBtn) {
        openBtn.addEventListener('click', function () {
            updateTotals();
            const total = parseFloat(document.getElementById('bulkExpectedTotal').value || '0');
            if (total <= 0) {
                Swal.fire({ icon: 'warning', title: @json(db_trans('please_fix_the_following_errors')), text: @json(db_trans('please_enter_at_least_one_amount')) });
                return;
            }
            if (modal) modal.show();
        });
    }

    const confirmBtn = document.getElementById('confirmBulkContributionSave');
    if (confirmBtn && form) {
        confirmBtn.addEventListener('click', function () {
            updateTotals();
            const variance = parseFloat(document.getElementById('modalBulkVarianceText').textContent || '0');
            if (Math.abs(variance) > 0.009) {
                Swal.fire({ icon: 'error', title: @json(db_trans('totals_do_not_match')), text: @json(db_trans('denomination_total_must_match_contribution_total')) });
                return;
            }
            form.submit();
        });
    }
});
</script>
@endpush
