<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationControlSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.manage_controls') ?? false;
    }

    public function rules(): array
    {
        return [
            'quiet_hours_enabled' => ['required', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
            'default_duplicate_window_hours' => ['required', 'integer', 'min:0', 'max:168'],
            'default_requires_approval' => ['required', 'boolean'],
            'approval_threshold_recipients' => ['required', 'integer', 'min:0'],
            'approval_threshold_segments' => ['required', 'integer', 'min:0'],
            'allow_retry_failed' => ['required', 'boolean'],
        ];
    }
}
