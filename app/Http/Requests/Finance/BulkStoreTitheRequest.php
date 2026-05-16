<?php

namespace App\Http\Requests\Finance;

use App\Models\Tithe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreTitheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kanda_id' => ['required', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['required', 'integer', 'exists:jumuiyas,id'],
            'contribution_date' => ['required', 'date'],

            'override_existing' => ['nullable', 'boolean'],
            'override_reason' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => (bool) $this->boolean('override_existing')),
            ],

            'send_sms' => ['nullable', 'boolean'],

            'rows' => ['required', 'array', 'min:1'],
            'rows.*.member_id' => ['required', 'integer', 'exists:members,id'],
            'rows.*.amount' => ['nullable', 'numeric', 'min:0'],
            'rows.*.notes' => ['nullable', 'string', 'max:1000'],
            'rows.*.status' => ['nullable', Rule::in(Tithe::availableStatuses())],
            'rows.*.payment_method' => ['nullable', Rule::in(Tithe::availablePaymentMethods())],

            'denominations' => ['required', 'array', 'min:1'],
            'denominations.*.value' => ['required', 'numeric', 'min:0.01'],
            'denominations.*.quantity' => ['required', 'integer', 'min:0'],

            'expected_total' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}