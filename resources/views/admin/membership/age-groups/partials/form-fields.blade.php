<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('name') }}</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ db_trans('minimum_age') }}</label>
        <input type="number" name="min_age" class="form-control" min="0" max="150" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ db_trans('maximum_age') }}</label>
        <input type="number" name="max_age" class="form-control" min="0" max="150" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('gender_scope') }}</label>
        <select name="gender_scope" class="form-select">
            @foreach($genderScopes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" checked>
            <label class="form-check-label">{{ db_trans('active') }}</label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">{{ db_trans('description') }}</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
</div>
