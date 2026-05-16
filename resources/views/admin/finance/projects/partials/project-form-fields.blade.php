@php
    $projectStatuses = $projectStatuses ?? \App\Models\Project::availableStatuses();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('project_category') }}</label>
        <select name="project_category_id" class="form-select" required>
            <option value="">{{ db_trans('select_project_category') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('project_category_id', $project?->project_category_id) === (int) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $project?->name) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('status') }}</label>
        <select name="status" class="form-select" required>
            @foreach($projectStatuses as $status)
                <option value="{{ $status }}" @selected(old('status', $project?->status ?? \App\Models\Project::STATUS_ACTIVE) === $status)>
                    {{ db_trans($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('start_date') }}</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($project?->start_date)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('end_date') }}</label>
        <input type="date" name="end_date" class="form-control" value="{{ old('end_date', optional($project?->end_date)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('budget_amount') }}</label>
        <input type="number" step="0.01" min="0" name="budget_amount" class="form-control" value="{{ old('budget_amount', $project?->budget_amount) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('target_amount') }}</label>
        <input type="number" step="0.01" min="0" name="target_amount" class="form-control" value="{{ old('target_amount', $project?->target_amount) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('active') }}</label>
        <select name="is_active" class="form-select">
            <option value="1" @selected((int) old('is_active', $project?->is_active ?? 1) === 1)>{{ db_trans('yes') }}</option>
            <option value="0" @selected((int) old('is_active', $project?->is_active ?? 1) === 0)>{{ db_trans('no') }}</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">{{ db_trans('description') }}</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $project?->description) }}</textarea>
    </div>
</div>