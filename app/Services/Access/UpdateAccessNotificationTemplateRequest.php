<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccessNotificationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access.templates.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'templates' => ['required', 'array', 'min:1'],
            'templates.*.id' => ['required', 'integer', 'exists:access_notification_templates,id'],
            'templates.*.name' => ['required', 'string', 'max:150'],
            'templates.*.message' => ['required', 'string'],
            'templates.*.is_active' => ['required', 'boolean'],
        ];
    }
}