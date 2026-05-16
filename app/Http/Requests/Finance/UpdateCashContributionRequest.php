<?php

namespace App\Http\Requests\Finance;

use App\Models\CashContribution;

class UpdateCashContributionRequest extends StoreCashContributionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.contributions.cash.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('status')) {
            $this->merge([
                'status' => CashContribution::normalizeStatus($this->input('status')),
            ]);
        }
    }
}