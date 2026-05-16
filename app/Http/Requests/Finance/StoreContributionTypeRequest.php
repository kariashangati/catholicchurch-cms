<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContributionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.contributions.types.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name', '')),
            'has_installments' => $this->boolean('has_installments'),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:contribution_types,name'],
            'slug' => ['required', 'string', 'max:160', 'unique:contribution_types,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'has_installments' => ['nullable', 'boolean'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'installments_count' => ['nullable', 'integer', 'min:1', 'max:60'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
