<?php

namespace App\Http\Requests\Finance\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GiversNonGiversReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.reports.waliotoa.view')
            || $this->user()?->can('reports.finance.view')
            || $this->user()?->can('finance.view')
            || false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'year' => (int) ($this->input('year') ?: now()->year),
            'month' => $this->filled('month') ? (int) $this->input('month') : null,
            'kanda_id' => $this->filled('kanda_id') ? (int) $this->input('kanda_id') : null,
            'jumuiya_id' => $this->filled('jumuiya_id') ? (int) $this->input('jumuiya_id') : null,
            'data_type' => $this->input('data_type') ?: 'tithe',
            'giver_status' => $this->input('giver_status') ?: 'all',
        ]);
    }

    public function rules(): array
    {
        return [
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'data_type' => ['required', 'string', 'max:80'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'giver_status' => ['required', Rule::in(['all', 'waliotoa', 'wasiotoa'])],
        ];
    }
}
