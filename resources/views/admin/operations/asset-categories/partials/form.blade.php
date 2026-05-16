<div class="mb-3">
    <label class="form-label">{{ db_trans('name') }}</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">{{ db_trans('description') }}</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
</div>
<div class="form-check form-switch">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ ($category->id ?? 'create') }}_asset_category_active" @checked(old('is_active', $category->is_active ?? true))>
    <label class="form-check-label" for="{{ ($category->id ?? 'create') }}_asset_category_active">{{ db_trans('active') }}</label>
</div>
