<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'offering_type_id' => ['nullable', 'exists:offering_types,id'],
            'mass_type_id' => ['nullable', 'exists:mass_types,id'],
            'collection_scope' => ['nullable', Rule::in(['parish', 'kanda', 'jumuiya'])],
            'kanda_id' => ['nullable', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'exists:jumuiyas,id'],
        ];
    }
}
