<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueReceiptsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.receipts.print') ?? false;
    }

    public function rules(): array
    {
        return [
            'receipt_type' => ['required', Rule::in(['zaka', 'mchango'])],
            'receipt_layout' => ['required', Rule::in(['mwanajumuiya', 'jumuiya', 'kanda'])],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'contribution_type_id' => ['nullable', 'integer', 'exists:contribution_types,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'source_keys' => ['nullable', 'array'],
            'source_keys.*' => ['string', 'max:100'],
            'send_sms' => ['nullable', 'boolean'],
            'mode' => ['nullable', Rule::in(['download', 'print'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($this->input('receipt_layout') === 'mwanajumuiya' && ! $this->filled('member_id') && empty($this->input('source_keys'))) {
                $validator->errors()->add('source_keys', db_trans('please_select_at_least_one_receipt'));
            }

            if ($this->input('receipt_layout') === 'jumuiya' && ! $this->filled('jumuiya_id')) {
                $validator->errors()->add('jumuiya_id', db_trans('receipt_jumuiya_required'));
            }

            if ($this->input('receipt_layout') === 'kanda' && ! $this->filled('kanda_id')) {
                $validator->errors()->add('kanda_id', db_trans('receipt_kanda_required'));
            }
        });
    }
}
