@php
    $editing = isset($teachingType) && $teachingType?->exists;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ db_trans('name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $teachingType->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ db_trans('slug') }}</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $teachingType->slug ?? '') }}" @disabled($editing && ($teachingType->is_system ?? false))>
        <small class="text-muted">{{ db_trans('mafundisho_slug_help') }}</small>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ db_trans('mafundisho_sacrament_link') }}</label>
        <select name="sacrament_key" class="form-select" @disabled($editing && ($teachingType->is_system ?? false))>
            <option value="">{{ db_trans('none') }}</option>
            @foreach($sacramentOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('sacrament_key', $teachingType->sacrament_key ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <small class="text-muted">{{ db_trans('mafundisho_sacrament_key_help') }}</small>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">{{ db_trans('mafundisho_eligibility_rule') }}</label>
        <select name="eligibility_rule" class="form-select" @disabled($editing && ($teachingType->is_system ?? false))>
            <option value="">{{ db_trans('mafundisho_eligibility_all') }}</option>
            @foreach($eligibilityOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('eligibility_rule', $teachingType->eligibility_rule ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">{{ db_trans('sort_order') }}</label>
        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $teachingType->sort_order ?? 0) }}">
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="requires_partner_info" value="0">
            <input class="form-check-input" type="checkbox" name="requires_partner_info" value="1" id="requiresPartner{{ $editing ? $teachingType->id : 'Create' }}" @checked(old('requires_partner_info', $teachingType->requires_partner_info ?? false))>
            <label class="form-check-label" for="requiresPartner{{ $editing ? $teachingType->id : 'Create' }}">{{ db_trans('mafundisho_requires_partner_info') }}</label>
        </div>
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive{{ $editing ? $teachingType->id : 'Create' }}" @checked(old('is_active', $teachingType->is_active ?? true))>
            <label class="form-check-label" for="isActive{{ $editing ? $teachingType->id : 'Create' }}">{{ db_trans('active') }}</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">{{ db_trans('description') }}</label>
        <textarea name="description" rows="3" class="form-control">{{ old('description', $teachingType->description ?? '') }}</textarea>
    </div>
</div>
