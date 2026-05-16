<?php

namespace App\Http\Requests\Liturgy;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMassScheduleAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.mass-schedules.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'mass_schedule_id' => ['required', 'integer', 'exists:mass_schedules,id'],
            'role_name' => ['required', 'string', 'max:120'],
            'assignable_type' => ['required', 'string', Rule::in([
                'member',
                'jumuiya',
                'kanda',
                'apostolic_group',
                'leadership_assignment',
            ])],
            'assignable_id' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
