@extends('layouts.admin')

@section('title', db_trans('create_admin_user'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/usercreate.css') }}">
@endpush

@section('content')
    <div class="access-form-shell">
        <div class="access-form-hero">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <h2 class="mb-2">{{ db_trans('create_admin_user') }}</h2>
                    <p class="mb-0 text-white-50">{{ db_trans('create_system_user_from_existing_member_record') }}</p>
                </div>
                <a href="{{ route('system-access.users.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('back_to_admin_users') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-access.users.store') }}" id="adminUserCreateForm">
            @csrf

            <div class="card access-form-card">
                <div class="card-body">
                    <div class="access-section-title">{{ db_trans('church_scope') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('scope') }}</label>
                            <select name="scope_type" id="scopeTypeSelect" class="form-select @error('scope_type') is-invalid @enderror" required>
                                @foreach($scopeTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('scope_type', $selectedScopeType) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('scope_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4" id="kandaFieldWrap">
                            <label class="form-label">{{ db_trans('select_kanda') }}</label>
                            <select name="kanda_id" id="kandaSelect" class="form-select @error('kanda_id') is-invalid @enderror">
                                <option value="">{{ db_trans('select_kanda') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected((string) old('kanda_id') === (string) $kanda->id)>
                                        {{ $kanda->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kanda_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4" id="jumuiyaFieldWrap">
                            <label class="form-label">{{ db_trans('select_jumuiya') }}</label>
                            <select name="jumuiya_id" id="jumuiyaSelect" class="form-select @error('jumuiya_id') is-invalid @enderror">
                                <option value="">{{ db_trans('select_jumuiya') }}</option>
                            </select>
                            @error('jumuiya_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('search_member') }}</label>
                            <select name="member_id" id="memberSelect" class="form-select @error('member_id') is-invalid @enderror" required>
                                <option value="">{{ db_trans('select_member') }}</option>
                            </select>
                            @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-3 access-member-preview" id="memberPreview">
                        <div class="access-member-preview-title">{{ db_trans('member_preview') }}</div>
                        <div class="access-member-preview-meta">{{ db_trans('select_member_to_preview_details') }}</div>
                    </div>
                </div>
            </div>

            <div class="card access-form-card">
                <div class="card-body">
                    <div class="access-section-title">{{ db_trans('account_details') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('locale') }}</label>
                            <select name="locale" class="form-select @error('locale') is-invalid @enderror" required>
                                <option value="sw" @selected(old('locale', 'sw') === 'sw')>Swahili</option>
                                <option value="en" @selected(old('locale') === 'en')>English</option>
                            </select>
                            @error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                                <option value="1" @selected((string) old('is_active', '1') === '1')>{{ db_trans('active') }}</option>
                                <option value="0" @selected((string) old('is_active') === '0')>{{ db_trans('inactive') }}</option>
                            </select>
                            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card access-form-card">
                <div class="card-body">
                    <div class="access-section-title">{{ db_trans('assign_roles') }}</div>

                    <div class="row g-3">
                        @foreach($roles as $role)
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="form-check border rounded-3 p-3">
                                    <input class="form-check-input" type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                           id="role_{{ $role->id }}" @checked(collect(old('role_ids', []))->contains($role->id))>
                                    <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('role_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card access-form-card">
                <div class="card-body">
                    <div class="access-section-title">{{ db_trans('password_setup') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('password_mode') }}</label>
                            <select name="password_mode" id="passwordModeSelect" class="form-select @error('password_mode') is-invalid @enderror">
                                <option value="generated" @selected(old('password_mode', 'generated') === 'generated')>{{ db_trans('generate_default_password') }}</option>
                                <option value="manual" @selected(old('password_mode') === 'manual')>{{ db_trans('set_password_manually') }}</option>
                            </select>
                            @error('password_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-4" id="passwordInputWrap" style="display:none;">
                            <label class="form-label">{{ db_trans('password') }}</label>
                            <input type="text" name="password" value="{{ old('password') }}" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card access-form-card">
                <div class="card-body">
                    <div class="access-section-title">{{ db_trans('notification') }}</div>

                    <div class="row g-3">
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
                                <option value="sw" @selected(old('sms_template_locale', 'sw') === 'sw')>Swahili</option>
                                <option value="en" @selected(old('sms_template_locale') === 'en')>English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('create_admin_user') }}
                </button>
                <a href="{{ route('system-access.users.index') }}" class="btn btn-outline-secondary px-4">
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
                members: @json(route('system-access.ajax.members')),
            };

            const labels = {
                selectJumuiya: @json(db_trans('select_jumuiya')),
                selectMember: @json(db_trans('select_member')),
                memberPreview: @json(db_trans('member_preview')),
                selectMemberPreview: @json(db_trans('select_member_to_preview_details')),
                memberCode: @json(db_trans('member_code')),
                phone: @json(db_trans('phone')),
                kanda: @json(db_trans('kanda')),
                jumuiya: @json(db_trans('jumuiya')),
                globalHint: @json(db_trans('global_scope')),
            };

            const els = {
                scopeType: document.getElementById('scopeTypeSelect'),
                kandaWrap: document.getElementById('kandaFieldWrap'),
                jumuiyaWrap: document.getElementById('jumuiyaFieldWrap'),
                kanda: document.getElementById('kandaSelect'),
                jumuiya: document.getElementById('jumuiyaSelect'),
                member: document.getElementById('memberSelect'),
                preview: document.getElementById('memberPreview'),
                passwordMode: document.getElementById('passwordModeSelect'),
                passwordWrap: document.getElementById('passwordInputWrap'),
            };

            const oldJumuiyaId = @json(old('jumuiya_id'));
            const oldMemberId = @json(old('member_id'));
            let loadedMembers = [];

            function togglePassword() {
                const manual = els.passwordMode.value === 'manual';
                els.passwordWrap.style.display = manual ? '' : 'none';
            }

            function resetJumuiyaOptions(placeholderOnly = true) {
                els.jumuiya.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = labels.selectJumuiya;
                els.jumuiya.appendChild(option);

                if (placeholderOnly) {
                    els.jumuiya.value = '';
                }
            }

            function resetMemberOptions() {
                loadedMembers = [];
                els.member.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = labels.selectMember;
                els.member.appendChild(option);
                els.member.value = '';
                renderMemberPreview(null);
            }

            function renderMemberPreview(member) {
                if (!member) {
                    els.preview.innerHTML = `
                        <div class="access-member-preview-title">${labels.memberPreview}</div>
                        <div class="access-member-preview-meta">${labels.selectMemberPreview}</div>
                    `;
                    return;
                }

                els.preview.innerHTML = `
                    <div class="access-member-preview-title">${member.name}</div>
                    <div class="access-member-preview-meta">
                        ${labels.memberCode}: ${member.member_code ?? '—'}<br>
                        ${labels.phone}: ${member.phone ?? '—'}<br>
                        ${labels.kanda}: ${member.kanda_name ?? '—'}<br>
                        ${labels.jumuiya}: ${member.jumuiya_name ?? '—'}
                    </div>
                `;
            }

            async function fetchJumuiyas(kandaId, selectedId = null) {
                resetJumuiyaOptions(false);

                if (!kandaId) {
                    els.jumuiya.value = '';
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

            async function fetchMembers(selectedId = null) {
                resetMemberOptions();

                const scopeType = els.scopeType.value;
                const url = new URL(routes.members, window.location.origin);

                url.searchParams.set('scope_type', scopeType);

                if (scopeType === 'kanda' && els.kanda.value) {
                    url.searchParams.set('kanda_id', els.kanda.value);
                }

                if (scopeType === 'jumuiya') {
                    if (els.kanda.value) {
                        url.searchParams.set('kanda_id', els.kanda.value);
                    }
                    if (els.jumuiya.value) {
                        url.searchParams.set('jumuiya_id', els.jumuiya.value);
                    }
                }

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                loadedMembers = await response.json();

                loadedMembers.forEach((member) => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.name} (${member.member_code ?? '—'})`;
                    if (String(selectedId ?? '') === String(member.id)) {
                        option.selected = true;
                    }
                    els.member.appendChild(option);
                });

                const selectedMember = loadedMembers.find((item) => String(item.id) === String(els.member.value));
                renderMemberPreview(selectedMember ?? null);
            }

            async function syncScopeUI() {
                const scopeType = els.scopeType.value;

                els.kandaWrap.style.display = scopeType === 'global' ? 'none' : '';
                els.jumuiyaWrap.style.display = scopeType === 'jumuiya' ? '' : 'none';

                if (scopeType === 'global') {
                    els.kanda.value = '';
                    resetJumuiyaOptions();
                }

                if (scopeType === 'kanda') {
                    resetJumuiyaOptions();
                }

                if (scopeType === 'jumuiya') {
                    await fetchJumuiyas(els.kanda.value, oldJumuiyaId);
                }

                await fetchMembers(oldMemberId);
            }

            els.scopeType.addEventListener('change', async () => {
                resetMemberOptions();
                if (els.scopeType.value !== 'jumuiya') {
                    resetJumuiyaOptions();
                }
                await syncScopeUI();
            });

            els.kanda.addEventListener('change', async () => {
                resetMemberOptions();

                if (els.scopeType.value === 'jumuiya') {
                    await fetchJumuiyas(els.kanda.value);
                } else {
                    resetJumuiyaOptions();
                }

                await fetchMembers();
            });

            els.jumuiya.addEventListener('change', async () => {
                await fetchMembers();
            });

            els.member.addEventListener('change', () => {
                const selectedMember = loadedMembers.find((item) => String(item.id) === String(els.member.value));
                renderMemberPreview(selectedMember ?? null);
            });

            els.passwordMode.addEventListener('change', togglePassword);

            togglePassword();
            syncScopeUI();
        })();
    </script>
@endpush