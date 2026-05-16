<?php

namespace App\Http\Requests\Finance;

use App\Models\Tithe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TitheFilterRequest extends FormRequest
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
            'date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(Tithe::availableStatuses())],
            'payment_method' => ['nullable', Rule::in(Tithe::availablePaymentMethods())],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'recorded_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}