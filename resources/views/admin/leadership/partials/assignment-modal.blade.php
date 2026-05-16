@php
    $assignment = $assignment ?? null;
    $statusOptions = \App\Models\LeadershipAssignment::availableStatuses();
    $selectedStatus = \App\Models\LeadershipAssignment::normalizeStatus(old('status', $assignment->status ?? \App\Models\LeadershipAssignment::STATUS_ACTIVE));
    $memberSearchId = $modalId . '_member_search';
    $memberSelectId = $modalId . '_member_select';
    $kandaSelectId = $modalId . '_kanda_select';
    $jumuiyaSelectId = $modalId . '_jumuiya_select';
@endphp

<div class="modal fade finance-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <div><h5 class="modal-title mb-1">{{ $title }}</h5></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('search_member') }}</label>
                            <input type="text" class="form-control js-leadership-member-search" id="{{ $memberSearchId }}" data-target="{{ $memberSelectId }}" placeholder="{{ db_trans('type_member_name_or_code') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('member') }}</label>
                            <select name="member_id" class="form-select" id="{{ $memberSelectId }}" required>
                                <option value="">{{ db_trans('select_member') }}</option>
                                @foreach($members as $member)
                                    @php
                                        $memberLabel = trim(($member->full_name ?? $member->name ?? ('#' . $member->id)) . ' ' . ($member->member_code ?? '') . ' ' . ($member->phone ?? ''));
                                    @endphp
                                    <option value="{{ $member->id }}" data-search="{{ strtolower($memberLabel) }}" @selected(old('member_id', $assignment->member_id ?? '') == $member->id)>
                                        {{ $member->full_name ?? $member->name ?? ('#' . $member->id) }}
                                        @if(!empty($member->member_code)) · {{ $member->member_code }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('linked_user') }}</label>
                            <select name="user_id" class="form-select">
                                <option value="">{{ db_trans('optional') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $assignment->user_id ?? '') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('position') }}</label>
                            <select name="leadership_position_id" class="form-select" required>
                                <option value="">{{ db_trans('select_option') }}</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" @selected(old('leadership_position_id', $assignment->leadership_position_id ?? '') == $position->id)>{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('scope') }}</label>
                            <select name="scope_type" class="form-select" required>
                                @foreach(['parish', 'kanda', 'jumuiya', 'apostolic_group', 'family', 'other'] as $scope)
                                    <option value="{{ $scope }}" @selected(old('scope_type', $assignment->scope_type ?? 'parish') === $scope)>{{ db_trans($scope) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ db_trans($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('kanda') }}</label>
                            <select name="kanda_id" class="form-select js-leadership-kanda" id="{{ $kandaSelectId }}" data-jumuiya-target="{{ $jumuiyaSelectId }}">
                                <option value="">—</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected(old('kanda_id', $assignment->kanda_id ?? '') == $kanda->id)>{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('jumuiya') }}</label>
                            <select name="jumuiya_id" class="form-select js-leadership-jumuiya" id="{{ $jumuiyaSelectId }}">
                                <option value="">—</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" data-kanda="{{ $jumuiya->kanda_id }}" @selected(old('jumuiya_id', $assignment->jumuiya_id ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('apostolic_group') }}</label>
                            <select name="apostolic_group_id" class="form-select">
                                <option value="">—</option>
                                @foreach($apostolicGroups as $group)
                                    <option value="{{ $group->id }}" @selected(old('apostolic_group_id', $assignment->apostolic_group_id ?? '') == $group->id)>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('started_at') }}</label>
                            <input type="date" name="started_at" class="form-control" value="{{ old('started_at', $assignment && $assignment->started_at ? $assignment->started_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('ended_at') }}</label>
                            <input type="date" name="ended_at" class="form-control" value="{{ old('ended_at', $assignment && $assignment->ended_at ? $assignment->ended_at->format('Y-m-d') : '') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('scope_label') }}</label>
                            <input type="text" name="scope_label" class="form-control" value="{{ old('scope_label', $assignment->scope_label ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $assignment->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-leadership-member-search').forEach(function (input) {
        input.addEventListener('input', function () {
            const select = document.getElementById(input.dataset.target);
            if (!select) return;
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
            if (selected && selected.hidden) select.value = '';
        });
    });

    const syncJumuiyas = function (kandaSelect) {
        const jumuiyaSelect = document.getElementById(kandaSelect.dataset.jumuiyaTarget);
        if (!jumuiyaSelect) return;
        const kanda = kandaSelect.value;
        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }
            option.hidden = !!kanda && option.dataset.kanda !== kanda;
        });
        const selected = jumuiyaSelect.options[jumuiyaSelect.selectedIndex];
        if (selected && selected.hidden) jumuiyaSelect.value = '';
    };

    document.querySelectorAll('.js-leadership-kanda').forEach(function (select) {
        syncJumuiyas(select);
        select.addEventListener('change', function () {
            syncJumuiyas(select);
        });
    });
});
</script>
@endpush
@endonce
