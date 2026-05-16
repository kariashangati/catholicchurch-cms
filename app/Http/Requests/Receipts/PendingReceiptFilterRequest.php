<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendingReceiptFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'source_type' => ['nullable', 'string', Rule::in(['tithe', 'cash_contribution', 'bank_contribution', 'offering'])],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'contribution_type_id' => ['nullable', 'integer', 'exists:contribution_types,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:2000'],
            'only_approved' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:200'],
            'sort' => ['nullable', 'string', Rule::in(['contribution_date', 'amount', 'member', 'source_type'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'only_approved' => $this->boolean('only_approved', true),
            'per_page' => $this->input('per_page', 25),
            'sort' => $this->input('sort', 'contribution_date'),
            'direction' => $this->input('direction', 'desc'),
        ]);
    }
}
