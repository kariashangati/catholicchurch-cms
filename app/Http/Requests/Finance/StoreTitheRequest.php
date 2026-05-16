<?php

namespace App\Http\Requests\Finance;

use App\Models\Tithe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTitheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'contribution_date' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(Tithe::availablePaymentMethods())],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'receipt_no' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(Tithe::availableStatuses())],
            'notes' => ['nullable', 'string'],
            'send_sms' => ['nullable', 'boolean'],
        ];
    }
}