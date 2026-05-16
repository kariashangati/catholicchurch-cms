<?php

namespace App\Http\Requests\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFooterCenterDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cms.footer.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'footer.tagline' => ['nullable', 'string'],
            'footer.description' => ['nullable', 'string'],
            'footer.newsletter_text' => ['nullable', 'string'],
            'footer.newsletter_note' => ['nullable', 'string'],
            'footer.bottom_note' => ['nullable', 'string'],

            'footer.youtube_url' => ['nullable', 'string', 'max:255'],
            'footer.facebook_url' => ['nullable', 'string', 'max:255'],
            'footer.instagram_url' => ['nullable', 'string', 'max:255'],
            'footer.tiktok_url' => ['nullable', 'string', 'max:255'],

            'church.email' => ['nullable', 'email', 'max:255'],
            'church.phone' => ['nullable', 'string', 'max:255'],
            'church.address' => ['nullable', 'string'],
            'contact.working_hours' => ['nullable', 'string'],
            'contact.map_embed' => ['nullable', 'string'],
        ];
    }
}