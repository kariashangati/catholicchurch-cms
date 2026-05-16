<?php

namespace App\Http\Requests\Liturgy;

use App\Models\MassSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMassScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.mass-schedules.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => MassSchedule::normalizeStatus($this->input('status')),
            'special_occasion' => $this->boolean('special_occasion'),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'mass_type_id' => ['required', 'integer', 'exists:mass_types,id'],
            'scheduled_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in(MassSchedule::availableStatuses())],
            'special_occasion' => ['nullable', 'boolean'],
        ];
    }
}
