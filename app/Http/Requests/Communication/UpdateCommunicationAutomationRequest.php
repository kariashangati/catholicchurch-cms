<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommunicationAutomationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.manage_automations') ?? false;
    }

    public function rules(): array
    {
        $automationId = $this->route('automation')?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:120', Rule::unique('communication_automations', 'code')->ignore($automationId)],
            'event_key' => ['required', 'string', 'max:100'],
            'channel' => ['required', Rule::in(['sms'])],
            'template_id' => ['nullable', 'integer', 'exists:communication_templates,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'trigger_mode' => ['required', Rule::in(['immediate', 'scheduled', 'manual_review'])],
            'delay_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'audience_type' => ['nullable', 'string', 'max:50'],
            'conditions' => ['nullable', 'array'],
            'respect_preferences' => ['nullable', 'boolean'],
            'respect_quiet_hours' => ['nullable', 'boolean'],
            'send_once_per_entity' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_enabled' => $this->boolean('is_enabled'),
            'respect_preferences' => $this->boolean('respect_preferences', true),
            'respect_quiet_hours' => $this->boolean('respect_quiet_hours'),
            'send_once_per_entity' => $this->boolean('send_once_per_entity'),
            'conditions' => $this->input('conditions', []),
        ]);
    }
}
