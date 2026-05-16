<?php

namespace App\Http\Requests\Finance\Budgets;

use App\Models\BudgetExpenseEstimate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetExpenseEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.budgets.expense.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category_group' => BudgetExpenseEstimate::normalizeGroup($this->input('category_group')),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:150'],
            'category_group' => ['required', Rule::in(BudgetExpenseEstimate::groupOptions())],
            'amount' => ['required', 'numeric', 'min:0'],
            'budget_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
