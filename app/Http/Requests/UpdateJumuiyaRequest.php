<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJumuiyaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jumuiyas.update') ?? false;
    }

    public function rules(): array
    {
        $jumuiya = $this->route('jumuiya');

        return [
            'kanda_id' => ['required', 'exists:kandas,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jumuiyas', 'name')->ignore($jumuiya->id),
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}