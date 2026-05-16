<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamiliaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('familias.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'form_context' => $this->input('form_context', 'familia_create'),
        ]);
    }

    public function rules(): array
    {
        return [
            'jumuiya_id' => ['required', 'exists:jumuiyas,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('familias', 'name')->where(fn ($query) => $query->where('jumuiya_id', $this->jumuiya_id)),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'envelope_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'form_context' => ['nullable', 'string', 'max:100'],
        ];
    }
}
