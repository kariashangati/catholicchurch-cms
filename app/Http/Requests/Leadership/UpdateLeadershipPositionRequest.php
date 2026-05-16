<?php

namespace App\Http\Requests\Leadership;

use App\Models\LeadershipPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadershipPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leadership.positions.update') ?? false;
    }

    public function rules(): array
    {
        /** @var LeadershipPosition $position */
        $position = $this->route('position');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('leadership_positions', 'name')->ignore($position?->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('leadership_positions', 'slug')->ignore($position?->id)],
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
