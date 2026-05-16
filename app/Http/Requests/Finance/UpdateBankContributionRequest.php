<?php

namespace App\Http\Requests\Finance;

use App\Models\BankContribution;
use Illuminate\Validation\Rule;

class UpdateBankContributionRequest extends StoreBankContributionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.contributions.bank.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('status')) {
            $this->merge([
                'status' => BankContribution::normalizeStatus($this->input('status')),
            ]);
        }
    }

    public function rules(): array
    {
        $bankContributionId = $this->route('bankContribution')?->id;
        $rules = parent::rules();

        $rules['reference_no'] = [
            'required',
            'string',
            'max:120',
            Rule::unique('bank_contributions', 'reference_no')->ignore($bankContributionId),
        ];

        $rules['status'] = ['nullable', Rule::in(BankContribution::availableStatuses())];

        return $rules;
    }
}