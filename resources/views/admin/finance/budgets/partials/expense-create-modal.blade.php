<div class="modal fade" id="createExpenseBudgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('finance.budgets.expense.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">{{ db_trans('add_expense_budget_estimate') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    @include('admin.finance.budgets.partials.expense-form', [
                        'record' => null,
                        'groupOptions' => $groupOptions,
                        'suggestedCategories' => $suggestedCategories,
                        'year' => $year,
                    ])
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
