<?php

namespace App\Http\Requests;

use App\Models\TeachingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeachingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('mafundisho-types.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'requires_partner_info' => $this->boolean('requires_partner_info'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:teaching_types,slug'],
            'description' => ['nullable', 'string'],
            'sacrament_key' => ['nullable', Rule::in(array_keys(TeachingType::sacramentOptions()))],
            'eligibility_rule' => ['nullable', Rule::in(array_keys(TeachingType::eligibilityOptions()))],
            'requires_partner_info' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
