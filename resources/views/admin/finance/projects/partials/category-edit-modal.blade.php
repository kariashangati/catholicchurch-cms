<div class="modal fade tithe-modal" id="editProjectCategoryModal{{ $category->id }}" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.categories.update', $category) }}">@csrf @method('PUT')
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('edit_project_category') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">{{ db_trans('name') }}</label><input class="form-control" name="name" value="{{ $category->name }}" required></div>
      <div class="mb-3"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3">{{ $category->description }}</textarea></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($category->is_active)><label class="form-check-label">{{ db_trans('active') }}</label></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('update') }}</button></div>
  </form></div></div>
</div>
