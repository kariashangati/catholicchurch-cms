<?php

namespace App\Http\Requests\Leadership;

use App\Models\LeadershipAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadershipAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leadership.assignments.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('status')) {
            $this->merge([
                'status' => LeadershipAssignment::normalizeStatus($this->input('status')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'leadership_position_id' => ['required', 'exists:leadership_positions,id'],
            'kanda_id' => ['nullable', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'exists:jumuiyas,id'],
            'apostolic_group_id' => ['nullable', 'exists:apostolic_groups,id'],
            'scope_type' => ['required', Rule::in(['parish', 'kanda', 'jumuiya', 'apostolic_group', 'family', 'other'])],
            'scope_label' => ['nullable', 'string', 'max:255'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'status' => ['required', Rule::in(LeadershipAssignment::availableStatuses())],
            'notes' => ['nullable', 'string'],
        ];
    }
}
