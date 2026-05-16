@php
    $isEdit = $mode === 'edit';

    $value = function ($field, $default = null) use ($member, $isEdit) {
        if ($isEdit && old('member_form_mode') === 'edit' && (string) old('modal_member_id') === (string) ($member?->id)) {
            return old($field, data_get($member, $field, $default));
        }

        if (! $isEdit && old('member_form_mode', 'create') === 'create') {
            return old($field, $default);
        }

        return data_get($member, $field, $default);
    };

    $checked = function ($field, $default = false) use ($member, $isEdit) {
        if ($isEdit && old('member_form_mode') === 'edit' && (string) old('modal_member_id') === (string) ($member?->id)) {
            return old($field, data_get($member, $field, $default));
        }

        if (! $isEdit && old('member_form_mode', 'create') === 'create') {
            return old($field, $default);
        }

        return (bool) data_get($member, $field, $default);
    };

    $genderValue = strtolower((string) $value('gender'));
    $familyRoleValue = strtolower((string) $value('family_role'));

    $dateOfBirthValue = $value('date_of_birth');
    if ($dateOfBirthValue instanceof \Illuminate\Support\Carbon) {
        $dateOfBirthValue = $dateOfBirthValue->format('Y-m-d');
    }
@endphp

<div class="row g-4">
    <div class="col-xl-7">
        <div class="member-form-card">
            <div class="member-form-card__header">
                <h5>{{ db_trans('personal_information') }}</h5>
                <p>{{ db_trans('member_directory') }}</p>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('first_name') }}</label>
                    <input type="text" name="first_name" class="form-control" value="{{ $value('first_name') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('middle_name') }}</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ $value('middle_name') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('last_name') }}</label>
                    <input type="text" name="last_name" class="form-control" value="{{ $value('last_name') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('gender') }}</label>
                    <select name="gender" class="form-select">
                        <option value="">{{ db_trans('select_gender') }}</option>
                        <option value="mwanaume" @selected(in_array($genderValue, ['male', 'mwanaume'], true))>
                            {{ db_trans('male') }}
                        </option>
                        <option value="mwanamke" @selected(in_array($genderValue, ['female', 'mwanamke'], true))>
                            {{ db_trans('female') }}
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ $value('phone') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('date_of_birth') }}</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ $dateOfBirthValue }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('occupation') }}</label>
                    <input type="text" name="occupation" class="form-control" value="{{ $value('occupation') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('family_role') }}</label>
                    <select name="family_role" class="form-select">
                        <option value="">{{ db_trans('select_family_role') }}</option>
                        <option value="baba" @selected(in_array($familyRoleValue, ['father', 'baba'], true))>
                            {{ db_trans('father') }}
                        </option>
                        <option value="mama" @selected(in_array($familyRoleValue, ['mother', 'mama'], true))>
                            {{ db_trans('mother') }}
                        </option>
                        <option value="mtoto" @selected(in_array($familyRoleValue, ['child', 'mtoto'], true))>
                            {{ db_trans('child') }}
                        </option>
                        <option value="nyingine" @selected(in_array($familyRoleValue, ['other', 'nyingine'], true))>
                            {{ db_trans('other') }}
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('familia') }}</label>
                    <select name="familia_id" class="form-select js-familia-select" required>
                        <option value="">{{ db_trans('select_familia') }}</option>
                        @foreach($familias as $familia)
                            <option value="{{ $familia->id }}" @selected((string) $value('familia_id') === (string) $familia->id)>
                                {{ $familia->name }} — {{ $familia->jumuiya?->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('bahasha') }}</label>
                    <input type="text" name="bahasha" class="form-control" value="{{ $value('bahasha') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('member_code') }}</label>
                    <input type="text" name="member_code" class="form-control" value="{{ $value('member_code') }}">
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch member-switch mt-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="is_active_{{ $modalId ?? ($member?->id ?? 'create') }}"
                            @checked($checked('is_active', true))
                        >
                        <label class="form-check-label" for="is_active_{{ $modalId ?? ($member?->id ?? 'create') }}">
                            {{ db_trans('active') }}
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ db_trans('notes') }}</label>
                    <textarea name="notes" rows="4" class="form-control">{{ $value('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="member-form-card h-100">
            <div class="member-form-card__header">
                <h5>{{ db_trans('sacraments') }}</h5>
                <p>{{ db_trans('sacrament_overview') }}</p>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="toggle-card">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_baptized"
                            value="1"
                            data-sacrament-toggle="is_baptized"
                            @checked($checked('is_baptized'))
                        >
                        <span>
                            <strong>{{ db_trans('baptized') }}</strong>
                            <small>{{ db_trans('member_sacrament_details') }}</small>
                        </span>
                    </label>
                </div>

                <div class="col-sm-6">
                    <label class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="has_communion" value="1" @checked($checked('has_communion'))>
                        <span>
                            <strong>{{ db_trans('communion') }}</strong>
                            <small>{{ db_trans('sacraments') }}</small>
                        </span>
                    </label>
                </div>

                <div class="col-sm-6">
                    <label class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="has_confirmation" value="1" @checked($checked('has_confirmation'))>
                        <span>
                            <strong>{{ db_trans('confirmation') }}</strong>
                            <small>{{ db_trans('sacraments') }}</small>
                        </span>
                    </label>
                </div>

                <div class="col-sm-6">
                    <label class="toggle-card">
                        <input class="form-check-input" type="checkbox" name="receives_eucharist" value="1" @checked($checked('receives_eucharist'))>
                        <span>
                            <strong>{{ db_trans('eucharist') }}</strong>
                            <small>{{ db_trans('sacraments') }}</small>
                        </span>
                    </label>
                </div>

                <div class="col-sm-12">
                    <label class="toggle-card">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_married"
                            value="1"
                            data-sacrament-toggle="is_married"
                            @checked($checked('is_married'))
                        >
                        <span>
                            <strong>{{ db_trans('married') }}</strong>
                            <small>{{ db_trans('member_sacrament_details') }}</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="row g-3 mt-1" data-sacrament-section="baptism">
                <div class="col-12"><hr></div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('baptism_certificate_number') }}</label>
                    <input type="text" name="baptism_certificate_number" class="form-control" value="{{ $value('baptism_certificate_number') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('baptism_parish') }}</label>
                    <input type="text" name="baptism_parish" class="form-control" value="{{ $value('baptism_parish') }}">
                </div>

                <div class="col-12">
                    <label class="form-label">{{ db_trans('baptism_diocese') }}</label>
                    <input type="text" name="baptism_diocese" class="form-control" value="{{ $value('baptism_diocese') }}">
                </div>
            </div>

            <div class="row g-3 mt-1" data-sacrament-section="marriage">
                <div class="col-12"><hr></div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('marriage_type') }}</label>
                    <input type="text" name="marriage_type" class="form-control" value="{{ $value('marriage_type') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('marriage_certificate_number') }}</label>
                    <input type="text" name="marriage_certificate_number" class="form-control" value="{{ $value('marriage_certificate_number') }}">
                </div>
            </div>
        </div>
    </div>
</div>