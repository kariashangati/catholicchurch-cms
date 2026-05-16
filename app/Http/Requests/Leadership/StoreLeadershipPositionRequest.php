<?php

namespace App\Http\Requests\Leadership;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadershipPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leadership.positions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:leadership_positions,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:leadership_positions,slug'],
            'level_type' => ['required', Rule::in(['parish', 'kanda', 'jumuiya', 'apostolic_group', 'family', 'other'])],
            'committee_type' => ['required', Rule::in(['executive', 'council', 'secretariat', 'other'])],
            'auto_role_name' => ['nullable', 'string', 'max:255'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_system' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
