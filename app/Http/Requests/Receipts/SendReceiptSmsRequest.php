<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;

class SendReceiptSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.send_sms') ?? false;
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
