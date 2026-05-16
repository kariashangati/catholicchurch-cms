<?php

namespace App\Http\Requests;

use App\Models\TeachingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeachingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('mafundisho-types.update');
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
        $teachingType = $this->route('teaching_type');
        $id = $teachingType instanceof TeachingType ? $teachingType->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('teaching_types', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'sacrament_key' => ['nullable', Rule::in(array_keys(TeachingType::sacramentOptions()))],
            'eligibility_rule' => ['nullable', Rule::in(array_keys(TeachingType::eligibilityOptions()))],
            'requires_partner_info' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
