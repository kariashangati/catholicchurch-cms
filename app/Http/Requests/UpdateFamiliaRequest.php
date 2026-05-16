<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFamiliaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('familias.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $familia = $this->route('familia');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'form_context' => $this->input('form_context', 'familia_edit_' . ($familia?->id ?? 'unknown')),
        ]);
    }

    public function rules(): array
    {
        $familia = $this->route('familia');

        return [
            'jumuiya_id' => ['required', 'exists:jumuiyas,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('familias', 'name')
                    ->ignore($familia->id)
                    ->where(fn ($query) => $query->where('jumuiya_id', $this->jumuiya_id)),
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
