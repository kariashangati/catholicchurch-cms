@php
    $item = $item ?? null;
    $statusOptions = \App\Models\BankContribution::availableStatuses();

    $selectedStatus = old(
        'status',
        $item
            ? \App\Models\BankContribution::normalizeStatus($item->status ?? null)
            : \App\Models\BankContribution::STATUS_VERIFIED
    );

    $modalSearchId = $modalId . '_member_search';
    $modalMemberSelectId = $modalId . '_member_select';
    $sendSmsId = $modalId . '_send_sms';
@endphp

<div class="modal fade finance-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">{{ $title }}</h5>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('search_member') }}</label>
                            <input
                                type="text"
                                class="form-control js-bank-member-search"
                                id="{{ $modalSearchId }}"
                                data-target="{{ $modalMemberSelectId }}"
                                placeholder="{{ db_trans('type_member_name_or_code') }}"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('member') }}</label>
                            <select name="member_id" class="form-select js-bank-member-select" id="{{ $modalMemberSelectId }}" required>
                                <option value="">{{ db_trans('select_member') }}</option>

                                @foreach($members as $member)
                                    @php
                                        $memberLabel = trim(($member->full_name ?? $member->name ?? ('#' . $member->id)) . ' ' . ($member->member_code ?? '') . ' ' . ($member->phone ?? ''));
                                    @endphp

                                    <option
                                        value="{{ $member->id }}"
                                        data-search="{{ strtolower($memberLabel) }}"
                                        @selected(old('member_id', $item->member_id ?? '') == $member->id)
                                    >
                                        {{ $member->full_name ?? $member->name ?? ('#' . $member->id) }}
                                        @if(!empty($member->member_code))
                                            · {{ $member->member_code }}
                                        @endif
                                        @if(!empty($member->phone))
                                            · {{ $member->phone }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('contribution_type') }}</label>
                            <select name="contribution_type_id" class="form-select" required>
                                <option value="">{{ db_trans('select_option') }}</option>

                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('contribution_type_id', $item->contribution_type_id ?? '') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('bank_account') }}</label>
                            <select name="bank_account_id" class="form-select" required>
                                <option value="">{{ db_trans('select_option') }}</option>

                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('bank_account_id', $item->bank_account_id ?? '') == $account->id)>
                                        {{ $account->display_name ?? $account->account_name ?? ('#' . $account->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('reference_no') }}</label>
                            <input
                                type="text"
                                name="reference_no"
                                class="form-control"
                                value="{{ old('reference_no', $item->reference_no ?? '') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('receipt_no') }}</label>
                            <input
                                type="text"
                                name="receipt_no"
                                class="form-control"
                                value="{{ old('receipt_no', $item->receipt_no ?? '') }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('amount') }}</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount', $item->amount ?? '') }}"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('contribution_date') }}</label>
                            <input
                                type="date"
                                name="contribution_date"
                                class="form-control"
                                value="{{ old('contribution_date', $item && $item->contribution_date ? $item->contribution_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select">
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                        {{ db_trans($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="1"
                                    name="send_sms"
                                    id="{{ $sendSmsId }}"
                                    @checked(old('send_sms'))
                                >
                                <label class="form-check-label" for="{{ $sendSmsId }}">
                                    {{ db_trans('send_sms_acknowledgement') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $item->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ db_trans('cancel') }}
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ db_trans('save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-bank-member-search').forEach(function (input) {
        input.addEventListener('input', function () {
            const targetId = input.dataset.target;
            const select = document.getElementById(targetId);

            if (!select) {
                return;
            }

            const search = input.value.toLowerCase().trim();

            Array.from(select.options).forEach(function (option) {
                if (!option.value) {
                    option.hidden = false;
                    return;
                }

                const text = (option.dataset.search || option.textContent || '').toLowerCase();
                option.hidden = search && !text.includes(search);
            });

            const selected = select.options[select.selectedIndex];

            if (selected && selected.hidden) {
                select.value = '';
            }
        });
    });
});
</script>
@endpushOnce