<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('category') }}</label>
        <select name="asset_category_id" class="form-select" required>
            <option value="">{{ db_trans('select_option') }}</option>
            @foreach($categories as $categoryOption)
                <option value="{{ $categoryOption->id }}" @selected(old('asset_category_id', $asset->asset_category_id ?? '') == $categoryOption->id)>{{ $categoryOption->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $asset->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('asset_code') }}</label>
        <input type="text" name="asset_code" class="form-control" value="{{ old('asset_code', $asset->asset_code ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('registration_number') }}</label>
        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $asset->registration_number ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ db_trans('acquisition_cost') }}</label>
        <input type="number" step="0.01" min="0" name="acquisition_cost" class="form-control" value="{{ old('acquisition_cost', $asset->acquisition_cost ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ db_trans('current_value') }}</label>
        <input type="number" step="0.01" min="0" name="current_value" class="form-control" value="{{ old('current_value', $asset->current_value ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ db_trans('acquisition_date') }}</label>
        <input type="date" name="acquisition_date" class="form-control" value="{{ old('acquisition_date', isset($asset->acquisition_date) ? optional($asset->acquisition_date)->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('condition_status') }}</label>
        <select name="condition_status" class="form-select" required>
            @foreach($conditionStatuses as $status)
                <option value="{{ $status }}" @selected(old('condition_status', $asset->condition_status ?? \App\Models\ChurchAsset::CONDITION_GOOD) === $status)>{{ db_trans($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('location') }}</label>
        <input type="text" name="location" class="form-control" value="{{ old('location', $asset->location ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">{{ db_trans('document_optional') }}</label>
        <input type="file" name="document" class="form-control">
        @if(!empty($asset?->document_path))
            <small class="text-muted">{{ db_trans('current_document') }}: {{ $asset->document_path }}</small>
        @endif
    </div>
    <div class="col-12">
        <label class="form-label">{{ db_trans('notes') }}</label>
        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $asset->notes ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ ($asset->id ?? 'create') }}_asset_active" @checked(old('is_active', $asset->is_active ?? true))>
            <label class="form-check-label" for="{{ ($asset->id ?? 'create') }}_asset_active">{{ db_trans('active') }}</label>
        </div>
    </div>
</div>
