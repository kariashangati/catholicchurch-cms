<?php

namespace App\Http\Requests\SystemConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.config.branding.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'site_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'site_favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,ico,webp', 'max:2048'],
            'site_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'remove_site_logo' => ['nullable', 'boolean'],
            'remove_site_favicon' => ['nullable', 'boolean'],
            'remove_site_image' => ['nullable', 'boolean'],
        ];
    }
}