@php
    $editing = isset($automation);
    $conditions = old('conditions', $automation->conditions ?? []);
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="comm-panel h-100">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $automation->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $automation->code ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Event</label>
                    <select name="event_key" id="event_key" class="form-select" required>
                        <option value="">Select event</option>
                        @foreach($eventOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('event_key', $automation->event_key ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Template</label>
                    <select name="template_id" class="form-select">
                        <option value="">No template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) old('template_id', $automation->template_id ?? '') === (string) $template->id)>
                                {{ $template->name }} ({{ $template->locale }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Trigger mode</label>
                    <select name="trigger_mode" id="trigger_mode" class="form-select">
                        @foreach($triggerModes as $value => $label)
                            <option value="{{ $value }}" @selected(old('trigger_mode', $automation->trigger_mode ?? 'immediate') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delay minutes</label>
                    <input type="number" name="delay_minutes" class="form-control" min="0" value="{{ old('delay_minutes', $automation->delay_minutes ?? 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Audience type</label>
                    <select name="audience_type" class="form-select">
                        <option value="">Select</option>
                        @foreach($audienceTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('audience_type', $automation->audience_type ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="4">{{ old('notes', $automation->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="comm-panel h-100">
            <h5 class="comm-panel-title">Controls</h5>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="is_enabled" @checked(old('is_enabled', $automation->is_enabled ?? false))>
                <label class="form-check-label" for="is_enabled">Enable automation</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="respect_preferences" value="1" id="respect_preferences" @checked(old('respect_preferences', $automation->respect_preferences ?? true))>
                <label class="form-check-label" for="respect_preferences">Respect member communication preferences</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="respect_quiet_hours" value="1" id="respect_quiet_hours" @checked(old('respect_quiet_hours', $automation->respect_quiet_hours ?? false))>
                <label class="form-check-label" for="respect_quiet_hours">Respect quiet hours</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="send_once_per_entity" value="1" id="send_once_per_entity" @checked(old('send_once_per_entity', $automation->send_once_per_entity ?? false))>
                <label class="form-check-label" for="send_once_per_entity">Send only once per entity</label>
            </div>

            <hr>

            <h6 class="mb-3">Conditions</h6>
            <div id="conditionSchemaWrap" class="small text-muted mb-2"></div>
            <div class="mb-3">
                <label class="form-label">Minimum amount</label>
                <input type="number" step="0.01" class="form-control" name="conditions[minimum_amount]" value="{{ data_get($conditions, 'minimum_amount') }}">
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="conditions[requires_approval]" value="1" id="requires_approval" @checked(data_get($conditions, 'requires_approval'))>
                <label class="form-check-label" for="requires_approval">Requires approval</label>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="conditions[only_active_members]" value="1" id="only_active_members" @checked(data_get($conditions, 'only_active_members'))>
                <label class="form-check-label" for="only_active_members">Only active members</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Tracked fields (comma separated)</label>
                <input type="text" class="form-control" name="conditions[fields]" value="{{ is_array(data_get($conditions, 'fields')) ? implode(', ', data_get($conditions, 'fields')) : data_get($conditions, 'fields') }}">
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="conditions[only_when_phone_changes]" value="1" id="only_when_phone_changes" @checked(data_get($conditions, 'only_when_phone_changes'))>
                <label class="form-check-label" for="only_when_phone_changes">Only when phone changes</label>
            </div>
        </div>
    </div>
</div>
