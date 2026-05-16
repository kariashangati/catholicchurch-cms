<?php

namespace App\Http\Requests\Admin\Communication;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleCommunicationCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.schedule') ?? false;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'schedule_timezone' => ['nullable', 'timezone'],
            'schedule_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'schedule_timezone' => $this->input('schedule_timezone') ?: config('app.timezone', 'UTC'),
        ]);
    }
}
