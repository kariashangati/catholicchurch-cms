<?php

namespace App\Http\Requests\Liturgy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreMassTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.mass-types.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name', '')),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:mass_types,name'],
            'slug' => ['required', 'string', 'max:170', 'unique:mass_types,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
