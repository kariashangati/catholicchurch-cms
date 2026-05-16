<?php

namespace App\Http\Requests\Finance;

use App\Models\BankContribution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.contributions.bank.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('status')) {
            $this->merge([
                'status' => BankContribution::normalizeStatus($this->input('status')),
            ]);
        }

        $this->merge([
            'send_sms' => $this->boolean('send_sms'),
        ]);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'familia_id' => ['nullable', 'integer', 'exists:familias,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'contribution_type_id' => ['required', 'integer', 'exists:contribution_types,id'],
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'contribution_date' => ['required', 'date'],
            'reference_no' => ['required', 'string', 'max:120', 'unique:bank_contributions,reference_no'],
            'receipt_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'send_sms' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(BankContribution::availableStatuses())],
        ];
    }
}