@php
    $prefix = $prefix ?? '';
    $group = $group ?? null;
    $oldPrefix = $oldPrefix ?? null;

    $kandas = $kandas ?? collect();
    $jumuiyas = $jumuiyas ?? collect();
    $familias = $familias ?? collect();
    $members = $members ?? collect();

    $oldOrGroup = function (string $key, $default = null) use ($group, $oldPrefix) {
        $oldKey = $oldPrefix ? $oldPrefix.'.'.$key : $key;
        return old($oldKey, data_get($group, $key, $default));
    };

    $memberSearchText = function ($member) {
        return strtolower(
            ($member->full_name ?? '') . ' ' .
            ($member->familia?->name ?? '') . ' ' .
            ($member->familia?->jumuiya?->name ?? '') . ' ' .
            ($member->familia?->jumuiya?->kanda?->name ?? '')
        );
    };

    $memberOptionLabel = function ($member) {
        return trim(
            ($member->full_name ?? '—') . ' — ' .
            ($member->familia?->name ?? '—') . ' — ' .
            ($member->familia?->jumuiya?->name ?? '—')
        );
    };
@endphp

<div class="row g-4">
    <div class="col-lg-6">
        <div class="apg-form-card">
            <div class="apg-form-card-title">{{ db_trans('basic_information') }}</div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('group_name') }}</label>
                <input type="text" class="form-control" name="name" id="{{ $prefix }}name" value="{{ $oldOrGroup('name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('group_code') }}</label>
                <input type="text" class="form-control" name="code" id="{{ $prefix }}code" value="{{ $oldOrGroup('code') }}" required>
                <div class="apg-form-hint">{{ db_trans('group_code_hint') }}</div>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('notes') }}</label>
                <textarea class="form-control" name="notes" id="{{ $prefix }}notes" rows="4">{{ $oldOrGroup('notes') }}</textarea>
            </div>

            <div class="mb-0">
                <label class="form-label">{{ db_trans('group_image') }}</label>
                <input type="file" class="form-control" name="image" id="{{ $prefix }}image" accept=".jpg,.jpeg,.png,.webp">
                @if($group?->image)
                    <div class="apg-form-hint mt-2">{{ db_trans('current_image') }}: {{ basename($group->image) }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="apg-form-card">
            <div class="apg-form-card-title">{{ db_trans('leadership_and_status') }}</div>

            <div class="mb-3 apg-member-picker" data-prefix="{{ $prefix }}" data-role="leader">
                <label class="form-label">{{ db_trans('group_leader') }}</label>

                <select class="form-select apg-member-select" name="leader_member_id" id="{{ $prefix }}leader_member_id">
                    <option value="">{{ db_trans('select_member') }}</option>
                    @foreach($members as $member)
                        <option
                            value="{{ $member->id }}"
                            data-kanda-id="{{ $member->familia?->jumuiya?->kanda_id }}"
                            data-jumuiya-id="{{ $member->familia?->jumuiya_id }}"
                            data-familia-id="{{ $member->familia_id }}"
                            data-search="{{ $memberSearchText($member) }}"
                            @selected((string) $oldOrGroup('leader_member_id') === (string) $member->id)
                        >
                            {{ $memberOptionLabel($member) }}
                        </option>
                    @endforeach
                </select>

                <div class="apg-member-filter-panel mt-2 p-3 rounded-3 border bg-light d-none">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('kanda') }}</label>
                            <select class="form-select form-select-sm apg-role-kanda-filter">
                                <option value="">{{ db_trans('all_kandas') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('jumuiya') }}</label>
                            <select class="form-select form-select-sm apg-role-jumuiya-filter">
                                <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda-id="{{ $jumuiya->kanda_id }}">
                                        {{ $jumuiya->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('familia') }}</label>
                            <select class="form-select form-select-sm apg-role-familia-filter">
                                <option value="">{{ db_trans('all_familias') }}</option>
                                @foreach($familias as $familia)
                                    <option
                                        value="{{ $familia->id }}"
                                        data-jumuiya-id="{{ $familia->jumuiya_id }}"
                                        data-kanda-id="{{ $familia->jumuiya?->kanda_id }}"
                                    >
                                        {{ $familia->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1">{{ db_trans('search_member') }}</label>
                            <input
                                type="text"
                                class="form-control form-control-sm apg-role-search-filter"
                                placeholder="{{ db_trans('search_by_name') ?: 'Search by name' }}"
                            >
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary apg-hide-member-filter">
                                {{ db_trans('close') ?: 'Close' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3 apg-member-picker" data-prefix="{{ $prefix }}" data-role="assistant">
                <label class="form-label">{{ db_trans('assistant_leader') }}</label>

                <select class="form-select apg-member-select" name="assistant_leader_member_id" id="{{ $prefix }}assistant_leader_member_id">
                    <option value="">{{ db_trans('select_member') }}</option>
                    @foreach($members as $member)
                        <option
                            value="{{ $member->id }}"
                            data-kanda-id="{{ $member->familia?->jumuiya?->kanda_id }}"
                            data-jumuiya-id="{{ $member->familia?->jumuiya_id }}"
                            data-familia-id="{{ $member->familia_id }}"
                            data-search="{{ $memberSearchText($member) }}"
                            @selected((string) $oldOrGroup('assistant_leader_member_id') === (string) $member->id)
                        >
                            {{ $memberOptionLabel($member) }}
                        </option>
                    @endforeach
                </select>

                <div class="apg-member-filter-panel mt-2 p-3 rounded-3 border bg-light d-none">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('kanda') }}</label>
                            <select class="form-select form-select-sm apg-role-kanda-filter">
                                <option value="">{{ db_trans('all_kandas') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('jumuiya') }}</label>
                            <select class="form-select form-select-sm apg-role-jumuiya-filter">
                                <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda-id="{{ $jumuiya->kanda_id }}">
                                        {{ $jumuiya->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('familia') }}</label>
                            <select class="form-select form-select-sm apg-role-familia-filter">
                                <option value="">{{ db_trans('all_familias') }}</option>
                                @foreach($familias as $familia)
                                    <option
                                        value="{{ $familia->id }}"
                                        data-jumuiya-id="{{ $familia->jumuiya_id }}"
                                        data-kanda-id="{{ $familia->jumuiya?->kanda_id }}"
                                    >
                                        {{ $familia->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1">{{ db_trans('search_member') }}</label>
                            <input
                                type="text"
                                class="form-control form-control-sm apg-role-search-filter"
                                placeholder="{{ db_trans('search_by_name') ?: 'Search by name' }}"
                            >
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary apg-hide-member-filter">
                                {{ db_trans('close') ?: 'Close' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3 apg-member-picker" data-prefix="{{ $prefix }}" data-role="patron">
                <label class="form-label">{{ db_trans('group_patron') }}</label>

                <select class="form-select apg-member-select" name="patron_member_id" id="{{ $prefix }}patron_member_id">
                    <option value="">{{ db_trans('select_member') }}</option>
                    @foreach($members as $member)
                        <option
                            value="{{ $member->id }}"
                            data-kanda-id="{{ $member->familia?->jumuiya?->kanda_id }}"
                            data-jumuiya-id="{{ $member->familia?->jumuiya_id }}"
                            data-familia-id="{{ $member->familia_id }}"
                            data-search="{{ $memberSearchText($member) }}"
                            @selected((string) $oldOrGroup('patron_member_id') === (string) $member->id)
                        >
                            {{ $memberOptionLabel($member) }}
                        </option>
                    @endforeach
                </select>

                <div class="apg-member-filter-panel mt-2 p-3 rounded-3 border bg-light d-none">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('kanda') }}</label>
                            <select class="form-select form-select-sm apg-role-kanda-filter">
                                <option value="">{{ db_trans('all_kandas') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('jumuiya') }}</label>
                            <select class="form-select form-select-sm apg-role-jumuiya-filter">
                                <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda-id="{{ $jumuiya->kanda_id }}">
                                        {{ $jumuiya->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1">{{ db_trans('familia') }}</label>
                            <select class="form-select form-select-sm apg-role-familia-filter">
                                <option value="">{{ db_trans('all_familias') }}</option>
                                @foreach($familias as $familia)
                                    <option
                                        value="{{ $familia->id }}"
                                        data-jumuiya-id="{{ $familia->jumuiya_id }}"
                                        data-kanda-id="{{ $familia->jumuiya?->kanda_id }}"
                                    >
                                        {{ $familia->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1">{{ db_trans('search_member') }}</label>
                            <input
                                type="text"
                                class="form-control form-control-sm apg-role-search-filter"
                                placeholder="{{ db_trans('search_by_name') ?: 'Search by name' }}"
                            >
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary apg-hide-member-filter">
                                {{ db_trans('close') ?: 'Close' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" value="1" name="is_active" id="{{ $prefix }}is_active" @checked((bool) $oldOrGroup('is_active', true))>
                <label class="form-check-label" for="{{ $prefix }}is_active">{{ db_trans('active_group') }}</label>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="apg-form-card">
            <div class="apg-form-card-title">{{ db_trans('membership_configuration') }}</div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('membership_rule') }}</label>
                <select class="form-select apg-rule-type" name="membership_rule_type" id="{{ $prefix }}membership_rule_type" data-prefix="{{ $prefix }}" required>
                    <option value="manual" @selected($oldOrGroup('membership_rule_type', 'manual') === 'manual')>{{ db_trans('manual_rule') }}</option>
                    <option value="gender" @selected($oldOrGroup('membership_rule_type') === 'gender')>{{ db_trans('gender_rule') }}</option>
                    <option value="family_role" @selected($oldOrGroup('membership_rule_type') === 'family_role')>{{ db_trans('family_role_rule') }}</option>
                </select>
            </div>

            <div class="mb-0 apg-rule-value-wrap" id="{{ $prefix }}ruleValueWrap">
                <label class="form-label">{{ db_trans('rule_value') }}</label>
                <input type="text" class="form-control" name="membership_rule_value" id="{{ $prefix }}membership_rule_value" value="{{ $oldOrGroup('membership_rule_value') }}" placeholder="{{ db_trans('example_rule_value') }}">
                <div class="apg-form-hint">{{ db_trans('membership_rule_value_hint') }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="apg-form-card">
            <div class="apg-form-card-title">{{ db_trans('meeting_information') }}</div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('founded_on') }}</label>
                <input type="date" class="form-control" name="founded_on" id="{{ $prefix }}founded_on" value="{{ optional($oldOrGroup('founded_on'))->format ? optional($oldOrGroup('founded_on'))->format('Y-m-d') : $oldOrGroup('founded_on') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('meeting_day') }}</label>
                <select class="form-select" name="meeting_day" id="{{ $prefix }}meeting_day">
                    <option value="">{{ db_trans('select_day') }}</option>
                    @foreach([
                        'Monday' => db_trans('monday'),
                        'Tuesday' => db_trans('tuesday'),
                        'Wednesday' => db_trans('wednesday'),
                        'Thursday' => db_trans('thursday'),
                        'Friday' => db_trans('friday'),
                        'Saturday' => db_trans('saturday'),
                        'Sunday' => db_trans('sunday'),
                    ] as $dayValue => $dayLabel)
                        <option value="{{ $dayValue }}" @selected($oldOrGroup('meeting_day') === $dayValue)>{{ $dayLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">{{ db_trans('meeting_time') }}</label>
                <input type="time" class="form-control" name="meeting_time" id="{{ $prefix }}meeting_time" value="{{ $oldOrGroup('meeting_time') }}">
            </div>

            <div class="mb-0">
                <label class="form-label">{{ db_trans('meeting_location') }}</label>
                <input type="text" class="form-control" name="meeting_location" id="{{ $prefix }}meeting_location" value="{{ $oldOrGroup('meeting_location') }}">
            </div>
        </div>
    </div>
</div>