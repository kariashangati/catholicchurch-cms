<?php

namespace App\Http\Requests\Finance\Reports;

use Illuminate\Foundation\Http\FormRequest;

class FinancialSummaryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.reports.summary.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ];
    }
}
