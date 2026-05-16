<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class TitheDuplicateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
        ];
    }
}
