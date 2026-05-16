<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJumuiyaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('jumuiyas.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'kanda_id' => ['required', 'exists:kandas,id'],
            'name' => ['required', 'string', 'max:255', 'unique:jumuiyas,name'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}