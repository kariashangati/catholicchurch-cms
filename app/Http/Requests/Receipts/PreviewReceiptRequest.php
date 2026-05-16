<?php

namespace App\Http\Requests\Receipts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receipts.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'receipt_type' => [
                'required',
                'string',
                Rule::in([
                    'tithe',
                    'cash_contribution',
                    'bank_contribution',
                    'offering',
                    'project_contribution',
                    'group_summary',
                ]),
            ],

            'source_type' => [
                'nullable',
                'string',
                Rule::in([
                    'tithe',
                    'cash_contribution',
                    'bank_contribution',
                    'offering',
                    'project_contribution',
                    'group_summary',
                ]),
            ],

            'receipt_layout' => [
                'required',
                'string',
                Rule::in([
                    'individual',
                    'jumuiya_summary',
                    'kanda_summary',
                ]),
            ],

            'scope_type' => [
                'nullable',
                'string',
                Rule::in([
                    'member',
                    'familia',
                    'jumuiya',
                    'kanda',
                ]),
            ],

            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'contribution_type_id' => ['nullable', 'integer', 'exists:contribution_types,id'],

            'source_record_ids' => ['nullable', 'array'],
            'source_record_ids.*' => ['integer', 'min:1'],

            'period' => ['nullable', 'date_format:Y-m'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'digits:4', 'min:2000'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $layout = $this->input('receipt_layout');

            if ($layout === 'individual' && ! $this->filled('member_id')) {
                $validator->errors()->add('member_id', db_trans('receipt_member_is_required_for_individual_layout'));
            }

            if ($layout === 'jumuiya_summary' && ! $this->filled('jumuiya_id')) {
                $validator->errors()->add('jumuiya_id', db_trans('receipt_jumuiya_is_required_for_jumuiya_summary_layout'));
            }

            if ($layout === 'kanda_summary' && ! $this->filled('kanda_id')) {
                $validator->errors()->add('kanda_id', db_trans('receipt_kanda_is_required_for_kanda_summary_layout'));
            }
        });
    }

    public function attributes(): array
    {
        return [
            'receipt_type' => db_trans('receipts.fields.receipt_type'),
            'source_type' => db_trans('receipts.fields.source_type'),
            'receipt_layout' => db_trans('receipts.fields.receipt_layout'),
            'scope_type' => db_trans('receipts.fields.scope'),
            'member_id' => db_trans('receipts.fields.member'),
            'familia_id' => db_trans('receipts.fields.familia'),
            'jumuiya_id' => db_trans('receipts.fields.jumuiya'),
            'kanda_id' => db_trans('receipts.fields.kanda'),
            'contribution_type_id' => db_trans('receipts.fields.contribution_type'),
            'period' => db_trans('receipts.fields.period'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('source_type') && $this->filled('receipt_type')) {
            $this->merge([
                'source_type' => $this->input('receipt_type'),
            ]);
        }

        if ($this->filled('period') && preg_match('/^(\\d{4})-(\\d{2})$/', (string) $this->input('period'), $matches)) {
            $this->merge([
                'year' => (int) $matches[1],
                'month' => (int) $matches[2],
            ]);
        }
    }
}