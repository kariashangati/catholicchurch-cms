<div class="modal fade tithe-modal" id="editProjectTransactionModal{{ $transaction->id }}" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content ui-modal-card"><form method="POST" action="{{ route('finance.projects.transactions.update', $transaction) }}">@csrf @method('PUT')
    <div class="modal-header ui-modal-header"><h5 class="modal-title ui-modal-title">{{ db_trans('edit_project_transaction') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-3">
      <div class="col-md-6"><label class="form-label">{{ db_trans('project') }}</label><select class="form-select" name="project_id" required>@foreach($projectOptions as $project)<option value="{{ $project->id }}" @selected($transaction->project_id == $project->id)>{{ $project->name }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">{{ db_trans('transaction_type') }}</label><select class="form-select" name="transaction_type">@foreach($transactionTypes as $type)<option value="{{ $type }}" @selected($transaction->transaction_type === $type)>{{ db_trans($type) }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">{{ db_trans('status') }}</label><select class="form-select" name="status">@foreach($transactionStatuses as $status)<option value="{{ $status }}" @selected($transaction->status === $status)>{{ db_trans($status) }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('amount') }}</label><input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ $transaction->amount }}" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('date') }}</label><input type="date" class="form-control" name="transaction_date" value="{{ optional($transaction->transaction_date)->format('Y-m-d') }}" required></div>
      <div class="col-md-4"><label class="form-label">{{ db_trans('payment_method') }}</label><input class="form-control" name="payment_method" value="{{ $transaction->payment_method }}"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('reference_no') }}</label><input class="form-control" name="reference_no" value="{{ $transaction->reference_no }}"></div>
      <div class="col-md-6"><label class="form-label">{{ db_trans('receipt_no') }}</label><input class="form-control" name="receipt_no" value="{{ $transaction->receipt_no }}"></div>
      <div class="col-12"><label class="form-label">{{ db_trans('description') }}</label><textarea class="form-control" name="description" rows="3">{{ $transaction->description }}</textarea></div>
    </div>
    <div class="modal-footer ui-modal-footer"><button class="btn ui-btn-primary">{{ db_trans('update') }}</button></div>
  </form></div></div>
</div>
