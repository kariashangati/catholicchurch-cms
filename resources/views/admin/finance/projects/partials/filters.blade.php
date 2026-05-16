<form method="GET" class="card ui-filter-card border-0 mb-4 admin-ui-v4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-end">
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('year') }}</label>
                <select name="year" class="form-select">
                    @for($yr = now()->year; $yr >= 2020; $yr--)
                        <option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>{{ $yr }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('month') }}</label>
                <select name="month" class="form-select">
                    <option value="">{{ db_trans('all_months') }}</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected(($filters['month'] ?? '') == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            @isset($categories)
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('category') }}</label>
                <select name="project_category_id" class="form-select">
                    <option value="">{{ db_trans('all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['project_category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            @endisset
            @isset($projects)
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('project') }}</label>
                <select name="project_id" class="form-select">
                    <option value="">{{ db_trans('all_projects') }}</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(($filters['project_id'] ?? '') == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            @endisset
            @isset($projectStatuses)
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('project_status') }}</label>
                <select name="project_status" class="form-select">
                    <option value="">{{ db_trans('all_statuses') }}</option>
                    @foreach($projectStatuses as $status)
                        <option value="{{ $status }}" @selected(($filters['project_status'] ?? '') === $status)>{{ db_trans($status) }}</option>
                    @endforeach
                </select>
            </div>
            @endisset
            @isset($transactionTypes)
            <div class="col-xl-2 col-md-4">
                <label class="form-label">{{ db_trans('transaction_type') }}</label>
                <select name="transaction_type" class="form-select">
                    <option value="">{{ db_trans('all_types') }}</option>
                    @foreach($transactionTypes as $type)
                        <option value="{{ $type }}" @selected(($filters['transaction_type'] ?? '') === $type)>{{ db_trans($type) }}</option>
                    @endforeach
                </select>
            </div>
            @endisset
            <div class="col-xl-2 col-md-4 d-grid"><button class="btn ui-btn-primary">{{ db_trans('apply_filters') }}</button></div>
        </div>
    </div>
</form>
