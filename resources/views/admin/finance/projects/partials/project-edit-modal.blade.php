<div class="modal fade tithe-modal" id="editProjectModal{{ $project->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.update', $project) }}">@csrf @method('PUT')
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('edit_project') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-3">
      <div class="col-md-6"><label class="form-label">{{ db_trans('category') }}</label><select class="form-select" name="project_category_id" required>@foreach($categoryOptions as $category)<option value="{{ $category->id }}" @selected($project->project_category_id == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('project_name') }}</label><input class="form-control" name="name" value="{{ $project->name }}" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('project_status') }}</label><select class="form-select" name="status">@foreach($projectStatuses as $status)<option value="{{ $status }}" @selected($project->status === $status)>{{ db_trans($status) }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('start_date') }}</label><input type="date" class="form-control" name="start_date" value="{{ optional($project->start_date)->format('Y-m-d') }}"></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('end_date') }}</label><input type="date" class="form-control" name="end_date" value="{{ optional($project->end_date)->format('Y-m-d') }}"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('budget_amount') }}</label><input type="number" step="0.01" min="0" class="form-control" name="budget_amount" value="{{ $project->budget_amount }}"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('target_amount') }}</label><input type="number" step="0.01" min="0" class="form-control" name="target_amount" value="{{ $project->target_amount }}"></div>
      <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3">{{ $project->description }}</textarea></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($project->is_active)><label class="form-check-label">{{ db_trans('active') }}</label></div></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('update') }}</button></div>
  </form></div></div>
</div>
