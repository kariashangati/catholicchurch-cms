<?php

namespace App\Http\Requests\Finance\Budgets;

class UpdateBudgetIncomeEstimateRequest extends StoreBudgetIncomeEstimateRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.budgets.income.update') ?? false;
    }
}
