@php
    $projectStatuses = $projectStatuses ?? \App\Models\Project::availableStatuses();
    $transactionStatuses = $transactionStatuses ?? \App\Models\ProjectTransaction::availableStatuses();
    $transactionTypes = $transactionTypes ?? \App\Models\ProjectTransaction::availableTypes();
    $paymentMethods = $paymentMethods ?? \App\Models\ProjectTransaction::availablePaymentMethods();
@endphp

<div class="modal fade" id="categoryCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.categories.store') }}">
                @csrf
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('add_project_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('name') }}</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="1">{{ db_trans('active') }}</option>
                                <option value="0">{{ db_trans('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="projectCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.store') }}">
                @csrf
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('add_project') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.finance.projects.partials.project-form-fields', ['project' => null])
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="transactionCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.transactions.store') }}">
                @csrf
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('add_project_transaction') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.finance.projects.partials.transaction-form-fields', ['transaction' => null])
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($categories as $category)
<div class="modal fade" id="categoryEditModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.categories.update', $category) }}">
                @csrf
                @method('PUT')
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('edit_project_category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="is_active" class="form-select">
                                <option value="1" @selected($category->is_active)>{{ db_trans('active') }}</option>
                                <option value="0" @selected(! $category->is_active)>{{ db_trans('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" class="form-control" rows="4">{{ $category->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($projects as $project)
<div class="modal fade" id="projectEditModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.update', $project) }}">
                @csrf
                @method('PUT')
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('edit_project') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.finance.projects.partials.project-form-fields', ['project' => $project])
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@foreach($transactionRows as $transaction)
<div class="modal fade" id="transactionEditModal{{ $transaction->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ route('finance.projects.transactions.update', $transaction) }}">
                @csrf
                @method('PUT')
                <div class="modal-header ui-modal-header">
                    <h5 class="ui-modal-title">{{ db_trans('edit_project_transaction') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.finance.projects.partials.transaction-form-fields', ['transaction' => $transaction])
                </div>
                <div class="modal-footer ui-modal-footer">
                    <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn ui-btn-primary">{{ db_trans('update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
