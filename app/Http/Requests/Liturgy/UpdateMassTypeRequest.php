<?php

namespace App\Http\Requests\Liturgy;

use App\Models\MassType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateMassTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.mass-types.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('name', '')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var MassType $massType */
        $massType = $this->route('mass_type');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('mass_types', 'name')->ignore($massType?->id)],
            'slug' => ['required', 'string', 'max:170', Rule::unique('mass_types', 'slug')->ignore($massType?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
