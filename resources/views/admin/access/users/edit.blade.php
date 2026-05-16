@extends('layouts.admin')

@section('title', db_trans('edit_admin_user'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
@endpush

@section('content')
    <div class="access-form-shell d-flex flex-column gap-4">
        <div class="access-form-hero p-4 rounded-4 text-white" style="background:linear-gradient(135deg,#0f172a 0%,#1d4ed8 45%,#0ea5e9 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2">{{ db_trans('edit_admin_user') }}</h2>
                    <p class="mb-0 text-white-50">{{ $adminUser->name }} • {{ $adminUser->email }}</p>
                </div>
                <a href="{{ route('system-access.users.show', $adminUser->id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('back_to_profile') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-access.users.update', $adminUser->id) }}" id="adminUserEditForm">
            @csrf
            @method('PUT')

            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">{{ db_trans('account_details') }}</h5>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('member') }}</label>
                            <input type="text" class="form-control" value="{{ $adminUser->member?->member_code }} - {{ $adminUser->name }}" disabled>
                            <div class="form-text">{{ db_trans('member') }} {{ db_trans('cannot_be_changed') ?? 'cannot be changed here.' }}</div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('current_scope') ?? 'Current scope' }}</label>
                            <input type="text" class="form-control" value="{{ $adminUser->display_scope }}" disabled>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('scope') }}</label>
                            <select name="scope_type" id="editScopeTypeSelect" class="form-select @error('scope_type') is-invalid @enderror" required>
                                @foreach($scopeTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('scope_type', $selectedScopeType) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scope_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4" id="editKandaFieldWrap">
                            <label class="form-label">{{ db_trans('select_kanda') }}</label>
                            <select name="kanda_id" id="editKandaSelect" class="form-select @error('kanda_id') is-invalid @enderror">
                                <option value="">{{ db_trans('select_kanda') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}"
                                        @selected((string) old('kanda_id', $adminUser->kanda_id) === (string) $kanda->id)>
                                        {{ $kanda->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kanda_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4" id="editJumuiyaFieldWrap">
                            <label class="form-label">{{ db_trans('select_jumuiya') }}</label>
                            <select name="jumuiya_id" id="editJumuiyaSelect" class="form-select @error('jumuiya_id') is-invalid @enderror">
                                <option value="">{{ db_trans('select_jumuiya') }}</option>
                            </select>
                            @error('jumuiya_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $adminUser->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('locale') }}</label>
                            <select name="locale" class="form-select @error('locale') is-invalid @enderror">
                                <option value="sw" @selected(old('locale', $adminUser->locale) === 'sw')>Swahili</option>
                                <option value="en" @selected(old('locale', $adminUser->locale) === 'en')>English</option>
                            </select>
                            @error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" @selected((string) old('is_active', $adminUser->is_active ? '1' : '0') === '1')>{{ db_trans('active') }}</option>
                                <option value="0" @selected((string) old('is_active', $adminUser->is_active ? '1' : '0') === '0')>{{ db_trans('inactive') }}</option>
                            </select>
                            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ db_trans('linked_member_scope_note') ?? 'Changing scope is allowed, but the linked member must still match the selected scope.' }}
                    </div>
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">{{ db_trans('assign_roles') }}</h5>

                    <div class="row g-3">
                        @foreach($roles as $role)
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="form-check border rounded-3 p-3">
                                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                           id="role_edit_{{ $role->id }}"
                                           @checked(collect(old('role_ids', $adminUser->roles->pluck('id')->all()))->contains($role->id))>
                                    <label class="form-check-label fw-semibold" for="role_edit_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('role_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card border-0 rounded-4 shadow-sm mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">{{ db_trans('password_setup') }}</h5>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('password_mode') }}</label>
                            <select name="password_mode" id="editPasswordModeSelect" class="form-select">
                                <option value="keep" @selected(old('password_mode', 'keep') === 'keep')>{{ db_trans('keep_current_password') }}</option>
                                <option value="generated" @selected(old('password_mode') === 'generated')>{{ db_trans('generate_new_password') }}</option>
                                <option value="manual" @selected(old('password_mode') === 'manual')>{{ db_trans('set_password_manually') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4" id="editPasswordInputWrap" style="display:none;">
                            <label class="form-label">{{ db_trans('password') }}</label>
                            <input type="text" name="password" value="{{ old('password') }}" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('send_sms_notification') }}</label>
                            <select name="send_sms" class="form-select">
                                <option value="0" @selected((string) old('send_sms', '0') === '0')>{{ db_trans('no') }}</option>
                                <option value="1" @selected((string) old('send_sms') === '1')>{{ db_trans('yes') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('sms_template_locale') }}</label>
                            <select name="sms_template_locale" class="form-select">
                                <option value="sw" @selected(old('sms_template_locale', $adminUser->locale) === 'sw')>Swahili</option>
                                <option value="en" @selected(old('sms_template_locale', $adminUser->locale) === 'en')>English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
                <a href="{{ route('system-access.users.show', $adminUser->id) }}" class="btn btn-outline-secondary px-4">
                    {{ db_trans('cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const routes = {
                jumuiyas: @json(route('system-access.ajax.jumuiyas')),
            };

            const els = {
                scopeType: document.getElementById('editScopeTypeSelect'),
                kandaWrap: document.getElementById('editKandaFieldWrap'),
                jumuiyaWrap: document.getElementById('editJumuiyaFieldWrap'),
                kanda: document.getElementById('editKandaSelect'),
                jumuiya: document.getElementById('editJumuiyaSelect'),
                passwordMode: document.getElementById('editPasswordModeSelect'),
                passwordWrap: document.getElementById('editPasswordInputWrap'),
            };

            const selectedJumuiyaId = @json(old('jumuiya_id', $adminUser->jumuiya_id));

            function togglePassword() {
                els.passwordWrap.style.display = els.passwordMode.value === 'manual' ? '' : 'none';
            }

            function resetJumuiyaOptions() {
                els.jumuiya.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = @json(db_trans('select_jumuiya'));
                els.jumuiya.appendChild(option);
            }

            async function fetchJumuiyas(kandaId, selectedId = null) {
                resetJumuiyaOptions();

                if (!kandaId) {
                    return;
                }

                const url = new URL(routes.jumuiyas, window.location.origin);
                url.searchParams.set('kanda_id', kandaId);

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const rows = await response.json();

                rows.forEach((row) => {
                    const option = document.createElement('option');
                    option.value = row.id;
                    option.textContent = row.name;
                    if (String(selectedId ?? '') === String(row.id)) {
                        option.selected = true;
                    }
                    els.jumuiya.appendChild(option);
                });
            }

            async function syncScopeUI() {
                const scopeType = els.scopeType.value;

                els.kandaWrap.style.display = scopeType === 'global' ? 'none' : '';
                els.jumuiyaWrap.style.display = scopeType === 'jumuiya' ? '' : 'none';

                if (scopeType === 'global') {
                    els.kanda.value = '';
                    resetJumuiyaOptions();
                    return;
                }

                if (scopeType === 'kanda') {
                    resetJumuiyaOptions();
                    return;
                }

                await fetchJumuiyas(els.kanda.value, selectedJumuiyaId);
            }

            els.scopeType.addEventListener('change', async () => {
                if (els.scopeType.value !== 'jumuiya') {
                    resetJumuiyaOptions();
                }
                await syncScopeUI();
            });

            els.kanda.addEventListener('change', async () => {
                if (els.scopeType.value === 'jumuiya') {
                    await fetchJumuiyas(els.kanda.value);
                } else {
                    resetJumuiyaOptions();
                }
            });

            els.passwordMode.addEventListener('change', togglePassword);

            togglePassword();
            syncScopeUI();
        })();
    </script>
@endpush