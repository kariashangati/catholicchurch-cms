<?php

namespace App\Http\Requests\Liturgy;

use App\Models\OfferingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateOfferingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.offering-types.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name', '')),
            'category' => OfferingType::normalizeCategory($this->input('category')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $offeringType = $this->route('offering_type');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('offering_types', 'name')->ignore($offeringType?->id)],
            'slug' => ['required', 'string', 'max:170', Rule::unique('offering_types', 'slug')->ignore($offeringType?->id)],
            'category' => ['required', 'string', Rule::in(OfferingType::availableCategories())],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
