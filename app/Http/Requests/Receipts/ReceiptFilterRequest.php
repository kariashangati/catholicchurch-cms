<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'receipt_type' => ['nullable', Rule::in(['zaka', 'mchango'])],
            'receipt_layout' => ['nullable', Rule::in(['mwanajumuiya', 'jumuiya', 'kanda'])],
            'status' => ['nullable', Rule::in(['all', 'pending', 'printed', 'sms_sent', 'opened', 'downloaded'])],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'contribution_type_id' => ['nullable', 'integer', 'exists:contribution_types,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:200'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'receipt_type' => $this->input('receipt_type', 'zaka'),
            'receipt_layout' => $this->input('receipt_layout', 'mwanajumuiya'),
            'status' => $this->input('status', 'pending'),
            'year' => $this->input('year', now()->year),
            'per_page' => $this->input('per_page', 25),
        ]);
    }
}
