<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;

class ReprintReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.reprint') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
            'mark_as_duplicate_copy' => ['nullable', 'boolean'],
            'notify_member' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mark_as_duplicate_copy' => $this->boolean('mark_as_duplicate_copy', true),
            'notify_member' => $this->boolean('notify_member'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'reason' => db_trans('receipts.fields.reason'),
            'mark_as_duplicate_copy' => db_trans('receipts.fields.mark_as_duplicate'),
            'notify_member' => db_trans('receipts.fields.notify_member'),
        ];
    }
}