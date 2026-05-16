<?php

namespace App\Http\Requests\SystemConfig;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.config.general.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:2000'],
            'site_image' => ['nullable', 'string', 'max:255'],
            'default_locale' => ['required', Rule::in(['en', 'sw'])],
            'fallback_locale' => ['required', Rule::in(['en', 'sw'])],
            'public_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}