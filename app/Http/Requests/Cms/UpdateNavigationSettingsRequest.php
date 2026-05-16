<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNavigationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.navigation.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'nav.show_home' => ['nullable', 'boolean'],
            'nav.show_history' => ['nullable', 'boolean'],
            'nav.show_giving' => ['nullable', 'boolean'],
            'nav.show_kanda' => ['nullable', 'boolean'],
            'nav.show_jumuiya' => ['nullable', 'boolean'],
            'nav.show_masses' => ['nullable', 'boolean'],
            'nav.show_announcements' => ['nullable', 'boolean'],
            'nav.show_projects' => ['nullable', 'boolean'],
            'nav.show_leadership' => ['nullable', 'boolean'],
            'nav.show_gallery' => ['nullable', 'boolean'],
            'nav.show_contact' => ['nullable', 'boolean'],
            'nav.show_login' => ['nullable', 'boolean'],
            'nav.show_ministries' => ['nullable', 'boolean'],
        ];
    }
}