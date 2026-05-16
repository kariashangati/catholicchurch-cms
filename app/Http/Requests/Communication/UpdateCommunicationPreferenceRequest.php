<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.manage_preferences') ?? false;
    }

    public function rules(): array
    {
        return [
            'preferred_locale' => ['nullable', 'in:en,sw'],
            'preferred_phone' => ['nullable', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'],
            'allow_sms' => ['nullable', 'boolean'],
            'allow_general_sms' => ['nullable', 'boolean'],
            'allow_finance_sms' => ['nullable', 'boolean'],
            'allow_reminder_sms' => ['nullable', 'boolean'],
            'allow_announcement_sms' => ['nullable', 'boolean'],
            'allow_automated_sms' => ['nullable', 'boolean'],
            'allow_manual_sms' => ['nullable', 'boolean'],
            'is_phone_verified' => ['nullable', 'boolean'],
            'opt_out_reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $booleanFields = [
            'allow_sms',
            'allow_general_sms',
            'allow_finance_sms',
            'allow_reminder_sms',
            'allow_announcement_sms',
            'allow_automated_sms',
            'allow_manual_sms',
            'is_phone_verified',
        ];

        $data = [];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $data[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }

        $this->merge($data);
    }
}
