@php
    $tithe = $tithe ?? null;

    $selectedMember = old('member_id', $tithe?->member_id ?? '');
    $selectedJumuiya = old('jumuiya_id', $tithe?->jumuiya_id ?? $tithe?->member?->familia?->jumuiya_id ?? '');
    $selectedKanda = old('kanda_id', $tithe?->member?->familia?->jumuiya?->kanda_id ?? $tithe?->jumuiya?->kanda_id ?? '');
    $selectedStatus = old('status', $tithe?->status ?? \App\Models\Tithe::STATUS_APPROVED);
    $selectedMethod = old('payment_method', $tithe?->payment_method ?? \App\Models\Tithe::PAYMENT_CASH);
    $selectedDate = old('contribution_date', $tithe?->contribution_date?->format('Y-m-d') ?? now()->toDateString());

    $formUid = 'tithe_form_' . ($tithe?->id ?? 'new') . '_' . str_replace('.', '', uniqid('', true));

    $memberRows = $members->map(function ($member) {
        return [
            'id' => $member->id,
            'name' => $member->full_name,
            'code' => $member->member_code,
            'phone' => $member->phone,
            'jumuiya_id' => $member->familia?->jumuiya_id,
            'jumuiya_name' => $member->familia?->jumuiya?->name,
            'kanda_id' => $member->familia?->jumuiya?->kanda_id,
        ];
    })->values();

    $statusLabels = [
        \App\Models\Tithe::STATUS_PENDING => db_trans('inasubiri'),
        \App\Models\Tithe::STATUS_APPROVED => db_trans('imeidhinishwa'),
        \App\Models\Tithe::STATUS_REJECTED => db_trans('imekataliwa'),
    ];

    $paymentLabels = [
        \App\Models\Tithe::PAYMENT_CASH => db_trans('taslimu'),
        \App\Models\Tithe::PAYMENT_BANK => db_trans('benki'),
        \App\Models\Tithe::PAYMENT_MOBILE => db_trans('simu'),
        \App\Models\Tithe::PAYMENT_OTHER => db_trans('nyingine'),
    ];
@endphp

<div
    class="row g-3 js-tithe-form-root"
    id="{{ $formUid }}"
    data-selected-member="{{ $selectedMember }}"
    data-selected-jumuiya="{{ $selectedJumuiya }}"
    data-selected-kanda="{{ $selectedKanda }}"
>
    <div class="col-md-4">
        <label class="form-label">{{ db_trans('kanda') }}</label>
        <select name="kanda_id" class="form-select js-tithe-kanda">
            <option value="">{{ db_trans('select_kanda') }}</option>
            @foreach($kandas as $kanda)
                <option value="{{ $kanda->id }}" @selected((string) $selectedKanda === (string) $kanda->id)>
                    {{ $kanda->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('jumuiya') }}</label>
        <select name="jumuiya_id" class="form-select js-tithe-jumuiya">
            <option value="">{{ db_trans('select_jumuiya') }}</option>
            @foreach($jumuiyas as $jumuiya)
                <option
                    value="{{ $jumuiya->id }}"
                    data-kanda-id="{{ $jumuiya->kanda_id }}"
                    @selected((string) $selectedJumuiya === (string) $jumuiya->id)
                >
                    {{ $jumuiya->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('search_member') }}</label>
        <input
            type="text"
            class="form-control js-tithe-member-search"
            placeholder="{{ db_trans('type_member_name_or_code') }}"
        >
    </div>

    <div class="col-md-12">
        <label class="form-label">{{ db_trans('member') }}</label>
        <select name="member_id" class="form-select js-tithe-member" required>
            <option value="">{{ db_trans('select_member') }}</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('amount') }}</label>
        <input
            type="number"
            step="0.01"
            min="1"
            name="amount"
            class="form-control"
            value="{{ old('amount', $tithe?->amount ?? '') }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('date') }}</label>
        <input
            type="date"
            name="contribution_date"
            class="form-control"
            value="{{ $selectedDate }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('payment_method') }}</label>
        <select name="payment_method" class="form-select">
            @foreach($paymentMethods as $method)
                <option value="{{ $method }}" @selected($selectedMethod === $method)>
                    {{ $paymentLabels[$method] ?? db_trans($method) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('status') }}</label>
        <select name="status" class="form-select">
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected($selectedStatus === $status)>
                    {{ $statusLabels[$status] ?? db_trans($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('reference_no') }}</label>
        <input
            type="text"
            name="reference_no"
            class="form-control"
            value="{{ old('reference_no', $tithe?->reference_no ?? '') }}"
        >
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('receipt_no') }}</label>
        <input
            type="text"
            name="receipt_no"
            class="form-control"
            value="{{ old('receipt_no', $tithe?->receipt_no ?? '') }}"
        >
    </div>

    <div class="col-md-12">
        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                value="1"
                name="send_sms"
                id="send_sms_{{ $formUid }}"
                @checked(old('send_sms'))
            >
            <label class="form-check-label" for="send_sms_{{ $formUid }}">
                {{ db_trans('send_sms_acknowledgement') }}
            </label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">{{ db_trans('notes') }}</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $tithe?->notes ?? '') }}</textarea>
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.titheMemberRows = @json($memberRows);

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.js-tithe-form-root').forEach(function (formRoot) {
                    const kandaSelect = formRoot.querySelector('.js-tithe-kanda');
                    const jumuiyaSelect = formRoot.querySelector('.js-tithe-jumuiya');
                    const memberSelect = formRoot.querySelector('.js-tithe-member');
                    const searchInput = formRoot.querySelector('.js-tithe-member-search');

                    if (!kandaSelect || !jumuiyaSelect || !memberSelect || !searchInput) {
                        return;
                    }

                    const members = window.titheMemberRows || [];
                    const selectedMember = String(formRoot.dataset.selectedMember || '');
                    const selectedJumuiya = String(formRoot.dataset.selectedJumuiya || '');
                    const selectedKanda = String(formRoot.dataset.selectedKanda || '');

                    function refreshJumuiyas() {
                        const kandaId = String(kandaSelect.value || '');

                        Array.from(jumuiyaSelect.options).forEach(function (option) {
                            if (!option.value) {
                                option.hidden = false;
                                return;
                            }

                            option.hidden = kandaId && String(option.dataset.kandaId || '') !== kandaId;
                        });

                        const selectedOption = jumuiyaSelect.options[jumuiyaSelect.selectedIndex];

                        if (selectedOption && selectedOption.hidden) {
                            jumuiyaSelect.value = '';
                        }
                    }

                    function memberLabel(member) {
                        const parts = [];

                        if (member.name) parts.push(member.name);
                        if (member.code) parts.push(member.code);
                        if (member.phone) parts.push(member.phone);
                        if (member.jumuiya_name) parts.push(member.jumuiya_name);

                        return parts.join(' · ');
                    }

                    function refreshMembers() {
                        const kandaId = String(kandaSelect.value || '');
                        const jumuiyaId = String(jumuiyaSelect.value || '');
                        const search = String(searchInput.value || '').toLowerCase().trim();

                        memberSelect.innerHTML = '';

                        const placeholder = document.createElement('option');
                        placeholder.value = '';
                        placeholder.textContent = @json(db_trans('select_member'));
                        memberSelect.appendChild(placeholder);

                        const filtered = members.filter(function (member) {
                            const matchesKanda = !kandaId || String(member.kanda_id || '') === kandaId;
                            const matchesJumuiya = !jumuiyaId || String(member.jumuiya_id || '') === jumuiyaId;

                            const haystack = [
                                member.name,
                                member.code,
                                member.phone,
                                member.jumuiya_name
                            ].filter(Boolean).join(' ').toLowerCase();

                            const matchesSearch = !search || haystack.includes(search);

                            return matchesKanda && matchesJumuiya && matchesSearch;
                        });

                        filtered.forEach(function (member) {
                            const option = document.createElement('option');
                            option.value = member.id;
                            option.textContent = memberLabel(member);

                            if (String(member.id) === selectedMember) {
                                option.selected = true;
                            }

                            memberSelect.appendChild(option);
                        });

                        if (filtered.length === 0) {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = @json(db_trans('no_members_found'));
                            option.disabled = true;
                            memberSelect.appendChild(option);
                        }
                    }

                    if (selectedKanda) {
                        kandaSelect.value = selectedKanda;
                    }

                    refreshJumuiyas();

                    if (selectedJumuiya) {
                        jumuiyaSelect.value = selectedJumuiya;
                    }

                    refreshMembers();

                    kandaSelect.addEventListener('change', function () {
                        refreshJumuiyas();
                        refreshMembers();
                    });

                    jumuiyaSelect.addEventListener('change', refreshMembers);
                    searchInput.addEventListener('input', refreshMembers);
                });
            });
        </script>
    @endpush
@endonce