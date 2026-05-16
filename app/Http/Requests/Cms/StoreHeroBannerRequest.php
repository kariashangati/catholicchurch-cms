<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeroBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.heroes.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'background_type' => ['required', 'in:image,video'],
            'background_value' => ['nullable', 'string', 'max:255'],
            'poster_image' => ['nullable', 'string', 'max:255'],
            'background_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,ogg', 'max:51200'],
            'poster_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'primary_button_text' => ['nullable', 'string', 'max:255'],
            'primary_button_link' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_link' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'overlay_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }
}
