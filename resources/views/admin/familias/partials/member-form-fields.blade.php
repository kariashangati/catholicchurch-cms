@php
    use Illuminate\Support\Str;

    $prefix = $prefix ?? '';
    $model = $model ?? null;
    $roles = $roles ?? ['baba', 'mama', 'mtoto', 'nyingine'];

    $oldOrModel = function ($key, $default = null) use ($model) {
        return old($key, data_get($model, $key, $default));
    };

    $isChecked = function ($key, $default = false) use ($oldOrModel) {
        return (bool) $oldOrModel($key, $default);
    };

    $selectedGender = Str::lower(trim((string) $oldOrModel('gender')));
    $selectedRole = Str::lower(trim((string) $oldOrModel('family_role')));

    $roleOptions = [
        'baba' => db_trans('father') ?: 'Baba',
        'mama' => db_trans('mother') ?: 'Mama',
        'mtoto' => db_trans('child') ?: 'Mtoto',
        'nyingine' => db_trans('other') ?: 'Nyingine',
    ];

    $roleAliases = [
        'baba' => ['baba', 'father'],
        'mama' => ['mama', 'mother'],
        'mtoto' => ['mtoto', 'child'],
        'nyingine' => ['nyingine', 'other'],
    ];
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="familia-form-card">
            <div class="familia-form-card-title">{{ db_trans('personal_information') }}</div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('first_name') }}</label>
                    <input
                        type="text"
                        class="form-control @error('first_name') is-invalid @enderror"
                        name="first_name"
                        id="{{ $prefix }}first_name"
                        value="{{ $oldOrModel('first_name') }}"
                        required
                    >
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('middle_name') }}</label>
                    <input
                        type="text"
                        class="form-control @error('middle_name') is-invalid @enderror"
                        name="middle_name"
                        id="{{ $prefix }}middle_name"
                        value="{{ $oldOrModel('middle_name') }}"
                    >
                    @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{ db_trans('last_name') }}</label>
                    <input
                        type="text"
                        class="form-control @error('last_name') is-invalid @enderror"
                        name="last_name"
                        id="{{ $prefix }}last_name"
                        value="{{ $oldOrModel('last_name') }}"
                        required
                    >
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('gender') }}</label>
                    <select
                        class="form-select @error('gender') is-invalid @enderror"
                        name="gender"
                        id="{{ $prefix }}gender"
                    >
                        <option value="">{{ db_trans('select_gender') }}</option>

                        <option value="mwanaume" @selected(in_array($selectedGender, ['male', 'mwanaume'], true))>
                            {{ db_trans('male') ?: 'Mwanaume' }}
                        </option>

                        <option value="mwanamke" @selected(in_array($selectedGender, ['female', 'mwanamke'], true))>
                            {{ db_trans('female') ?: 'Mwanamke' }}
                        </option>
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('family_role') }}</label>
                    <select
                        class="form-select @error('family_role') is-invalid @enderror"
                        name="family_role"
                        id="{{ $prefix }}family_role"
                    >
                        <option value="">{{ db_trans('select_family_role') }}</option>

                        @foreach($roleOptions as $value => $label)
                            <option value="{{ $value }}" @selected(in_array($selectedRole, $roleAliases[$value] ?? [$value], true))>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('family_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('phone') }}</label>
                    <input
                        type="text"
                        class="form-control @error('phone') is-invalid @enderror"
                        name="phone"
                        id="{{ $prefix }}phone"
                        value="{{ $oldOrModel('phone') }}"
                    >
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('date_of_birth') }}</label>
                    <input
                        type="date"
                        class="form-control @error('date_of_birth') is-invalid @enderror"
                        name="date_of_birth"
                        id="{{ $prefix }}date_of_birth"
                        value="{{ $oldOrModel('date_of_birth') }}"
                    >
                    @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('occupation') }}</label>
                    <input
                        type="text"
                        class="form-control @error('occupation') is-invalid @enderror"
                        name="occupation"
                        id="{{ $prefix }}occupation"
                        value="{{ $oldOrModel('occupation') }}"
                    >
                    @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('member_code') }}</label>
                    <input
                        type="text"
                        class="form-control @error('member_code') is-invalid @enderror"
                        name="member_code"
                        id="{{ $prefix }}member_code"
                        value="{{ $oldOrModel('member_code') }}"
                        placeholder="{{ db_trans('auto_generated_if_left_blank') }}"
                    >
                    @error('member_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('bahasha') }}</label>
                    <input
                        type="text"
                        class="form-control @error('bahasha') is-invalid @enderror"
                        name="bahasha"
                        id="{{ $prefix }}bahasha"
                        value="{{ $oldOrModel('bahasha') }}"
                    >
                    @error('bahasha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="familia-form-feature w-100 mb-0">
                        <div>
                            <div class="familia-form-feature-title">{{ db_trans('active_member') }}</div>
                            <div class="familia-form-feature-text">{{ db_trans('appears_in_family_directory') }}</div>
                        </div>

                        <div class="form-check form-switch m-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                value="1"
                                name="is_active"
                                id="{{ $prefix }}is_active"
                                @checked($isChecked('is_active', true))
                            >
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">{{ db_trans('notes') }}</label>
                    <textarea
                        class="form-control @error('notes') is-invalid @enderror"
                        name="notes"
                        id="{{ $prefix }}notes"
                        rows="3"
                    >{{ $oldOrModel('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="familia-form-card">
            <div class="familia-form-card-title">{{ db_trans('sacrament_information') }}</div>

            <div class="familia-toggle-grid mb-3">
                @foreach([
                    'is_baptized' => db_trans('baptized'),
                    'has_communion' => db_trans('communion'),
                    'has_confirmation' => db_trans('confirmation'),
                    'receives_eucharist' => db_trans('receiving_eucharist'),
                    'is_married' => db_trans('married'),
                ] as $field => $label)
                    <label class="familia-toggle-pill">
                        <input
                            class="form-check-input familia-sacrament-toggle"
                            type="checkbox"
                            value="1"
                            name="{{ $field }}"
                            data-target="#{{ $prefix }}{{ $field }}_details"
                            @checked($isChecked($field))
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div
                class="familia-sacrament-box {{ $isChecked('is_baptized') ? '' : 'd-none' }}"
                id="{{ $prefix }}is_baptized_details"
            >
                <div class="familia-sacrament-box-title">{{ db_trans('baptism_details') }}</div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ db_trans('baptism_certificate_number') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="baptism_certificate_number"
                            id="{{ $prefix }}baptism_certificate_number"
                            value="{{ $oldOrModel('baptism_certificate_number') }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('baptism_parish') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="baptism_parish"
                            id="{{ $prefix }}baptism_parish"
                            value="{{ $oldOrModel('baptism_parish') }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('baptism_diocese') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="baptism_diocese"
                            id="{{ $prefix }}baptism_diocese"
                            value="{{ $oldOrModel('baptism_diocese') }}"
                        >
                    </div>
                </div>
            </div>

            <div
                class="familia-sacrament-box {{ $isChecked('is_married') ? '' : 'd-none' }}"
                id="{{ $prefix }}is_married_details"
            >
                <div class="familia-sacrament-box-title">{{ db_trans('marriage_details') }}</div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">{{ db_trans('marriage_type') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="marriage_type"
                            id="{{ $prefix }}marriage_type"
                            value="{{ $oldOrModel('marriage_type') }}"
                        >
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ db_trans('marriage_certificate_number') }}</label>
                        <input
                            type="text"
                            class="form-control"
                            name="marriage_certificate_number"
                            id="{{ $prefix }}marriage_certificate_number"
                            value="{{ $oldOrModel('marriage_certificate_number') }}"
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>