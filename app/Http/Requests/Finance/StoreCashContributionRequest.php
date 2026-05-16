<?php

namespace App\Http\Requests\Finance;

use App\Models\CashContribution;
use Illuminate\Foundation\Http\FormRequest;

class StoreCashContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.contributions.cash.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('status')) {
            $this->merge([
                'status' => CashContribution::normalizeStatus($this->input('status')),
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'contribution_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'send_sms' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:' . implode(',', CashContribution::availableStatuses())],
        ];
    }
}