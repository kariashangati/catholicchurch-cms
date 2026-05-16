<?php

namespace App\Http\Requests\SystemConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.config.maintenance.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'global_enabled' => $this->boolean('global_enabled'),
            'use_laravel_down' => $this->boolean('use_laravel_down'),
        ]);
    }

    public function rules(): array
    {
        return [
            'global_enabled' => ['required', 'boolean'],
            'global_title' => ['nullable', 'string', 'max:255'],
            'global_message' => ['nullable', 'string', 'max:2000'],
            'use_laravel_down' => ['nullable', 'boolean'],

            'routes' => ['nullable', 'array'],
            'routes.*.name' => ['nullable', 'string', 'max:255'],
            'routes.*.enabled' => ['nullable', 'boolean'],
            'routes.*.title' => ['nullable', 'string', 'max:255'],
            'routes.*.message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
