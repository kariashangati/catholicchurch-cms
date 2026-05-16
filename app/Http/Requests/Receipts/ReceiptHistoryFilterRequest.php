<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptHistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'receipt_no' => ['nullable', 'string', 'max:100'],
            'source_type' => ['nullable', 'string', Rule::in(['tithe', 'cash_contribution', 'bank_contribution', 'offering', 'summary'])],
            'status' => ['nullable', 'string', Rule::in(['pending_issue', 'issued', 'printed', 'sms_sent', 'opened', 'downloaded', 'reprinted', 'voided', 'exception'])],
            'delivery_channel' => ['nullable', 'string', Rule::in(['print', 'sms_link'])],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'issued_by' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:200'],
            'sort' => ['nullable', 'string', Rule::in(['issued_at', 'receipt_no', 'amount', 'status', 'created_at'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => $this->input('per_page', 25),
            'sort' => $this->input('sort', 'issued_at'),
            'direction' => $this->input('direction', 'desc'),
        ]);
    }
}
