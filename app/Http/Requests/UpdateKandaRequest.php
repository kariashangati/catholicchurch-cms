<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKandaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kandas.update') ?? false;
    }

    public function rules(): array
    {
        $kanda = $this->route('kanda');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kandas', 'name')->ignore($kanda->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z]+$/',
                Rule::unique('kandas', 'code')->ignore($kanda->id),
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}