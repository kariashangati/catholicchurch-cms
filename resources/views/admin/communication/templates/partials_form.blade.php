@php
    $statuses = [
        \App\Models\CommunicationTemplate::STATUS_DRAFT => db_trans('communication.templates.status_draft'),
        \App\Models\CommunicationTemplate::STATUS_ACTIVE => db_trans('communication.templates.status_active'),
        \App\Models\CommunicationTemplate::STATUS_INACTIVE => db_trans('communication.templates.status_inactive'),
        \App\Models\CommunicationTemplate::STATUS_ARCHIVED => db_trans('communication.templates.status_archived'),
    ];

    $categories = [
        'thank_you' => db_trans('communication.templates.category_thank_you'),
        'reminder' => db_trans('communication.templates.category_reminder'),
        'announcement' => db_trans('communication.templates.category_announcement'),
        'member_update' => db_trans('communication.templates.category_member_update'),
        'leadership' => db_trans('communication.templates.category_leadership'),
        'general' => db_trans('communication.templates.category_general'),
    ];
@endphp

<div class="communication-template-form">
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 template-form-card template-form-glass h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="template-section-head mb-4">
                        <span class="template-section-pill"><i class="fas fa-pen-nib me-2"></i>{{ db_trans('communication.templates.content_section') }}</span>
                        <h4 class="template-section-title mb-1">{{ db_trans('communication.templates.design_message') }}</h4>
                        <p class="template-section-subtitle mb-0">{{ db_trans('communication.templates.design_message_subtitle') }}</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.name') }}</label>
                            <input type="text" name="name" class="form-control template-input" value="{{ old('name', $template->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.code') }}</label>
                            <input type="text" name="code" class="form-control template-input" value="{{ old('code', $template->code) }}" placeholder="tithe_thank_you_sw">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('communication.templates.channel') }}</label>
                            <select name="channel" class="form-select template-input">
                                <option value="sms" @selected(old('channel', $template->channel) === 'sms')>SMS</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('communication.templates.category') }}</label>
                            <select name="category" class="form-select template-input">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $template->category) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('communication.templates.locale') }}</label>
                            <select name="locale" class="form-select template-input">
                                <option value="sw" @selected(old('locale', $template->locale) === 'sw')>Swahili</option>
                                <option value="en" @selected(old('locale', $template->locale) === 'en')>English</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.event_key') }}</label>
                            <input type="text" name="event_key" class="form-control template-input" value="{{ old('event_key', $template->event_key) }}" placeholder="tithe.recorded">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.audience_type') }}</label>
                            <input type="text" name="audience_type" class="form-control template-input" value="{{ old('audience_type', $template->audience_type) }}" placeholder="member">
                        </div>

                        <div class="col-md-12 d-none">
                            <label class="form-label">{{ db_trans('communication.templates.subject') }}</label>
                            <input type="text" name="subject" id="template-subject" class="form-control template-input" value="{{ old('subject', $template->subject) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('communication.templates.body') }}</label>
                            <textarea name="body" id="template-body" rows="7" class="form-control template-input template-textarea" required>{{ old('body', $template->body) }}</textarea>
                            <small class="text-muted">{{ db_trans('communication.templates.placeholder_help') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.status') }}</label>
                            <select name="status" class="form-select template-input">
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $template->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('communication.templates.variables') }}</label>
                            <input
                                type="text"
                                name="variables[]"
                                class="form-control template-input"
                                value="{{ implode(', ', old('variables', $template->variables ?? $detectedVariables ?? [])) }}"
                                data-template-variables-input
                            >
                            <small class="text-muted">{{ db_trans('communication.templates.variables_help') }}</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('communication.templates.notes') }}</label>
                            <textarea name="notes" rows="3" class="form-control template-input">{{ old('notes', $template->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="template-sidebar-stack">
                <div class="card border-0 template-form-card template-preview-card mb-4">
                    <div class="card-body p-4">
                        <div class="template-section-head mb-3">
                            <span class="template-section-pill template-section-pill-alt"><i class="fas fa-eye me-2"></i>{{ db_trans('communication.templates.live_preview') }}</span>
                            <h5 class="template-preview-title mb-1">{{ db_trans('communication.templates.preview_title') }}</h5>
                            <p class="template-preview-subtitle mb-0">{{ db_trans('communication.templates.preview_subtitle') }}</p>
                        </div>

                        <div class="sms-preview-device">
                            <div class="sms-preview-topbar">
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                            </div>

                            <div class="sms-preview-bubble" id="template-preview-body">
                                {{ old('body', $template->body ?: db_trans('communication.templates.preview_empty')) }}
                            </div>

                            <div class="sms-preview-meta mt-3">
                                <div><strong>{{ db_trans('communication.templates.detected_variables') }}:</strong></div>
                                <div id="template-preview-variables">{{ implode(', ', $detectedVariables ?? $template->variables ?? []) ?: '—' }}</div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-template-preview w-100 mt-4"
                            data-template-preview-url="{{ route('admin.communication.templates.preview') }}"
                        >
                            <i class="fas fa-bolt me-2"></i>{{ db_trans('communication.templates.refresh_preview') }}
                        </button>
                    </div>
                </div>

                <div class="card border-0 template-form-card template-kpi-card">
                    <div class="card-body p-4">
                        <div class="template-section-head mb-3">
                            <span class="template-section-pill"><i class="fas fa-layer-group me-2"></i>{{ db_trans('communication.templates.quick_tokens') }}</span>
                            <h5 class="template-preview-title mb-1">{{ db_trans('communication.templates.token_library') }}</h5>
                        </div>

                        <div class="template-token-grid">
                            @foreach(['member_name','first_name','amount','month_name','familia_name','jumuiya_name','kanda_name','parish_name','date'] as $token)
                                @php
                                    $tokenPlaceholder = '{{ ' . $token . ' }}';
                                @endphp
                                <button
                                    class="template-token-chip"
                                    type="button"
                                    data-insert-token="{{ $tokenPlaceholder }}"
                                >
                                    {{ $tokenPlaceholder }}
                                </button>
                            @endforeach
                        </div>

                        <div class="template-footer-actions mt-4">
                            <button class="btn btn-template-submit w-100" type="submit">
                                <i class="fas fa-save me-2"></i>{{ $submitLabel }}
                            </button>

                            <a href="{{ route('admin.communication.templates.index') }}" class="btn btn-template-light w-100 mt-2">
                                <i class="fas fa-arrow-left me-2"></i>{{ db_trans('communication.templates.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>