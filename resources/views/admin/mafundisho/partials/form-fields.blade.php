@php
    $prefix = $prefix ?? '';
    $defaultType = $defaultType ?? null;
    $defaultYear = $defaultYear ?? now()->year;
    $defaultTeachingTypeId = $defaultTeachingTypeId ?? null;
    $isEdit = ($prefix ?? '') === 'edit_';

    $defaultTeachingType = null;

    if ($defaultTeachingTypeId) {
        $defaultTeachingType = $teachingTypes->firstWhere('id', (int) $defaultTeachingTypeId);
    }

    if (!$defaultTeachingType && $defaultType) {
        $defaultTeachingType = $teachingTypes->firstWhere('slug', $defaultType);
    }

    $defaultTeachingType = $defaultTeachingType ?: $teachingTypes->first();
@endphp

<input type="hidden" name="form_context" value="{{ $isEdit ? 'edit_enrollment' : 'create_enrollment' }}">
<input type="hidden" name="type" id="{{ $prefix }}type" value="{{ old($prefix . 'type', $defaultTeachingType?->slug) }}">

<div class="row g-4">
    <div class="col-12 {{ $isEdit ? 'd-none' : '' }}">
        <label class="form-label fw-bold">{{ db_trans('select_students') }}</label>

        <div class="teaching-member-picker">
            <div class="table-responsive">
                <table class="table align-middle mb-0 {{ $isEdit ? '' : 'teaching-member-picker-table' }}">
                    <thead>
                        <tr>
                            <th style="width:52px;">{{ db_trans('select') }}</th>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('member_code') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('familia') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                            <tr>
                                <td>
                                    <div class="form-check mb-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="member_ids[]"
                                            value="{{ $member->id }}"
                                            id="{{ $prefix }}member_pick_{{ $member->id }}"
                                            {{ in_array($member->id, old('member_ids', [])) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <label class="mb-0 fw-semibold" for="{{ $prefix }}member_pick_{{ $member->id }}">
                                        {{ $member->full_name }}
                                    </label>
                                </td>
                                <td>{{ $member->member_code ?? '—' }}</td>
                                <td>{{ $member->phone ?? '—' }}</td>
                                <td>{{ $member->familia?->name ?? '—' }}</td>
                                <td>{{ $member->familia?->jumuiya?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="small text-muted mt-2">{{ db_trans('you_can_select_multiple_students') }}</div>
        </div>
    </div>

    <div class="col-md-6 {{ $isEdit ? '' : 'd-none' }}">
        <label for="{{ $prefix }}member_id" class="form-label fw-bold">{{ db_trans('member') }}</label>
        <select name="member_id" id="{{ $prefix }}member_id" class="form-select" {{ $isEdit ? 'required' : '' }}>
            <option value="">{{ db_trans('select_member') }}</option>
            @foreach($members as $member)
                <option value="{{ $member->id }}">
                    {{ $member->full_name }} — {{ $member->member_code ?? '—' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="{{ $prefix }}teaching_type_id" class="form-label fw-bold">{{ db_trans('teaching_type') }}</label>
        <select name="teaching_type_id" id="{{ $prefix }}teaching_type_id" class="form-select teaching-type-select" required>
            @foreach($teachingTypes as $item)
                <option value="{{ $item->id }}"
                        data-slug="{{ $item->slug }}"
                        data-requires-partner-info="{{ $item->requires_partner_info ? '1' : '0' }}"
                        @selected((int) $defaultTeachingType?->id === (int) $item->id)>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="{{ $prefix }}year" class="form-label fw-bold">{{ db_trans('year') }}</label>
        <select name="year" id="{{ $prefix }}year" class="form-select" required>
            @foreach(range(now()->year + 1, 1990) as $yearOption)
                <option value="{{ $yearOption }}" @selected((int) $defaultYear === (int) $yearOption)>
                    {{ $yearOption }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="{{ $prefix }}status" class="form-label fw-bold">{{ db_trans('status') }}</label>
        <select name="status" id="{{ $prefix }}status" class="form-select teaching-status-select" required>
            @foreach($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected($value === 'continuing')>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label for="{{ $prefix }}started_at" class="form-label fw-bold">{{ db_trans('started_on') }}</label>
        <input type="date" name="started_at" id="{{ $prefix }}started_at" class="form-control">
    </div>

    <div class="col-md-3 teaching-completed-only {{ $prefix }}completed-only" style="display:none;">
        <label for="{{ $prefix }}ended_at" class="form-label fw-bold">{{ db_trans('completed_on') }}</label>
        <input type="date" name="ended_at" id="{{ $prefix }}ended_at" class="form-control">
    </div>

    <div class="col-12 teaching-section-card {{ $prefix }}marriage-only" style="display:none;">
        <div class="teaching-section-card-title">
            <i class="fas fa-ring me-2"></i>{{ db_trans('partner_information') }}
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="{{ $prefix }}partner_name" class="form-label fw-bold">{{ db_trans('partner_name') }}</label>
                <input type="text" name="partner_name" id="{{ $prefix }}partner_name" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="{{ $prefix }}partner_jumuiya" class="form-label fw-bold">{{ db_trans('partner_jumuiya') }}</label>
                <input type="text" name="partner_jumuiya" id="{{ $prefix }}partner_jumuiya" class="form-control">
            </div>

            <div class="col-md-4">
                <label for="{{ $prefix }}partner_phone" class="form-label fw-bold">{{ db_trans('partner_phone') }}</label>
                <input type="text" name="partner_phone" id="{{ $prefix }}partner_phone" class="form-control">
            </div>
        </div>
    </div>

    <div class="col-12">
        <label for="{{ $prefix }}notes" class="form-label fw-bold">{{ db_trans('notes') }}</label>
        <textarea name="notes" id="{{ $prefix }}notes" rows="4" class="form-control" placeholder="{{ db_trans('teaching_notes_placeholder') }}"></textarea>
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('change', function (event) {
    if (!event.target.classList.contains('teaching-type-select')) {
        return;
    }

    const prefix = event.target.id.replace('teaching_type_id', '');
    const selected = event.target.options[event.target.selectedIndex];
    const hiddenType = document.getElementById(prefix + 'type');

    if (hiddenType && selected) {
        hiddenType.value = selected.dataset.slug || '';
    }
});
</script>
@endPushOnce