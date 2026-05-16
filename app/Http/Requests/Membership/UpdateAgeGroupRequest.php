<?php

namespace App\Http\Requests\Membership;

use App\Models\AgeGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgeGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('membership.age-groups.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gender_scope' => AgeGroup::normalizeGenderScope($this->input('gender_scope')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $ageGroup = $this->route('age_group');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('age_groups', 'name')->ignore($ageGroup?->id)],
            'min_age' => ['required', 'integer', 'min:0', 'max:150'],
            'max_age' => ['required', 'integer', 'min:0', 'max:150', 'gte:min_age'],
            'gender_scope' => ['nullable', Rule::in(array_keys(AgeGroup::genderScopes()))],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
