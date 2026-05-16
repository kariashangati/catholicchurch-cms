<?php

namespace App\Http\Requests\Finance\Budgets;

class UpdateBudgetExpenseEstimateRequest extends StoreBudgetExpenseEstimateRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.budgets.expense.update') ?? false;
    }
}
