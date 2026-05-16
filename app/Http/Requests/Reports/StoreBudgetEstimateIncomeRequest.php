<?php

namespace App\Http\Requests\Reports;

use App\Services\Reports\BudgetEstimateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetEstimateIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'group_name' => $this->input('group_name', BudgetEstimateService::GROUP_ORDINARY),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_code' => ['required', 'string', 'max:50', Rule::in(BudgetEstimateService::allowedIncomeSourceCodes())],
            'category_name' => ['required', 'string', 'max:255'],
            'group_name' => ['required', 'string', 'max:100', Rule::in(BudgetEstimateService::allowedGroupCodes())],
            'amount' => ['required', 'numeric', 'min:0'],
            'budget_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
