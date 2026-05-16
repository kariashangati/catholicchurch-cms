<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.homepage.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'hero_badge' => ['nullable', 'string', 'max:255'],
            'enable_video' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'poster_url' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'mobile_image_url' => ['nullable', 'string', 'max:255'],
            'overlay_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'cta_primary_text' => ['nullable', 'string', 'max:255'],
            'cta_primary_link' => ['nullable', 'string', 'max:255'],
            'cta_secondary_text' => ['nullable', 'string', 'max:255'],
            'cta_secondary_link' => ['nullable', 'string', 'max:255'],

            'video_file' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:51200'],
            'poster_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'mobile_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],

            'homepage.show_announcements' => ['nullable', 'boolean'],
            'homepage.show_masses' => ['nullable', 'boolean'],
            'homepage.show_projects' => ['nullable', 'boolean'],
            'homepage.show_ministries' => ['nullable', 'boolean'],
            'homepage.show_gallery' => ['nullable', 'boolean'],
            'homepage.show_leadership' => ['nullable', 'boolean'],
            'homepage.show_contact' => ['nullable', 'boolean'],
        ];
    }
}
