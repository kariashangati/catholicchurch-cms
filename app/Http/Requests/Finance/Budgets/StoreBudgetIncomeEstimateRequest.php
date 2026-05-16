<?php

namespace App\Http\Requests\Finance\Budgets;

use App\Models\BudgetIncomeEstimate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetIncomeEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.budgets.income.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_type' => BudgetIncomeEstimate::normalizeSource($this->input('source_type')),
            'category_group' => BudgetIncomeEstimate::normalizeGroup($this->input('category_group')),
        ]);
    }

    public function rules(): array
    {
        return [
            'source_type' => ['required', Rule::in(BudgetIncomeEstimate::sourceOptions())],
            'category_name' => ['required', 'string', 'max:150'],
            'category_group' => ['required', Rule::in(BudgetIncomeEstimate::groupOptions())],
            'amount' => ['required', 'numeric', 'min:0'],
            'budget_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
