<?php

namespace App\Http\Requests\Liturgy;

use App\Models\OfferingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreOfferingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.offering-types.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name', '')),
            'category' => OfferingType::normalizeCategory($this->input('category')),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:offering_types,name'],
            'slug' => ['required', 'string', 'max:170', 'unique:offering_types,slug'],
            'category' => ['required', 'string', Rule::in(OfferingType::availableCategories())],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
