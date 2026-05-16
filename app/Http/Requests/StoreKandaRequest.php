<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kandas.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:kandas,name'],
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+$/', 'unique:kandas,code'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}