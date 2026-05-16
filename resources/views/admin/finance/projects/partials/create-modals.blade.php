<div class="modal fade tithe-modal" id="createProjectCategoryModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.categories.store') }}">@csrf
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('add_project_category') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="mb-3"><label class="form-label">{{ db_trans('name') }}</label><input class="form-control" name="name" required></div>
      <div class="mb-3"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">{{ db_trans('active') }}</label></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('save') }}</button></div>
  </form></div></div>
</div>

<div class="modal fade tithe-modal" id="createProjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.store') }}">@csrf
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('add_project') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-3">
      <div class="col-md-6"><label class="form-label">{{ db_trans('category') }}</label><select class="form-select" name="project_category_id" required>@foreach($categoryOptions as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('project_name') }}</label><input class="form-control" name="name" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('project_status') }}</label><select class="form-select" name="status">@foreach($projectStatuses as $status)<option value="{{ $status }}">{{ db_trans($status) }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('start_date') }}</label><input type="date" class="form-control" name="start_date"></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('end_date') }}</label><input type="date" class="form-control" name="end_date"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('budget_amount') }}</label><input type="number" step="0.01" min="0" class="form-control" name="budget_amount"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('target_amount') }}</label><input type="number" step="0.01" min="0" class="form-control" name="target_amount"></div>
      <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3"></textarea></div>
      <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">{{ db_trans('active') }}</label></div></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('save') }}</button></div>
  </form></div></div>
</div>

<div class="modal fade tithe-modal" id="createProjectTransactionModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.transactions.store') }}">@csrf
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('add_project_transaction') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-3">
      <div class="col-md-6"><label class="form-label">{{ db_trans('project') }}</label><select class="form-select" name="project_id" required>@foreach($projectOptions as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">{{ db_trans('transaction_type') }}</label><select class="form-select" name="transaction_type">@foreach($transactionTypes as $type)<option value="{{ $type }}">{{ db_trans($type) }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">{{ db_trans('status') }}</label><select class="form-select" name="status">@foreach($transactionStatuses as $status)<option value="{{ $status }}">{{ db_trans($status) }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('amount') }}</label><input type="number" step="0.01" min="0.01" class="form-control" name="amount" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('date') }}</label><input type="date" class="form-control" name="transaction_date" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('payment_method') }}</label><input class="form-control" name="payment_method"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('reference_no') }}</label><input class="form-control" name="reference_no"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('receipt_no') }}</label><input class="form-control" name="receipt_no"></div>
      <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3"></textarea></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('save') }}</button></div>
  </form></div></div>
</div>
