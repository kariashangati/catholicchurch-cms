<?php

namespace App\Http\Requests\Finance;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionBatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $source = (string) $this->input('source_type', ContributionBatch::SOURCE_CASH);

        if ($source === ContributionBatch::SOURCE_BANK) {
            return $this->user()?->can('finance.contributions.bank.create') ?? false;
        }

        return $this->user()?->can('finance.contributions.cash.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $source = $this->input('source_type', ContributionBatch::SOURCE_CASH);
        $status = $this->input('status');

        if ($source === ContributionBatch::SOURCE_BANK && filled($status)) {
            $status = BankContribution::normalizeStatus($status);
        }

        if ($source === ContributionBatch::SOURCE_CASH && filled($status)) {
            $status = CashContribution::normalizeStatus($status);
        }

        $this->merge([
            'source_type' => $source,
            'status' => $status,
            'send_sms' => $this->boolean('send_sms'),
        ]);
    }

    public function rules(): array
    {
        $source = (string) $this->input('source_type', ContributionBatch::SOURCE_CASH);
        $statusOptions = $source === ContributionBatch::SOURCE_BANK
            ? BankContribution::availableStatuses()
            : CashContribution::availableStatuses();

        return [
            'kanda_id' => ['required', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['required', 'integer', 'exists:jumuiyas,id'],
            'contribution_type_id' => ['required', 'integer', 'exists:contribution_types,id'],
            'source_type' => ['required', Rule::in(ContributionBatch::availableSources())],
            'bank_account_id' => [Rule::requiredIf(fn () => $source === ContributionBatch::SOURCE_BANK), 'nullable', 'integer', 'exists:bank_accounts,id'],
            'contribution_date' => ['required', 'date'],
            'status' => ['nullable', Rule::in($statusOptions)],
            'send_sms' => ['nullable', 'boolean'],
            'expected_total' => ['required', 'numeric', 'min:0.01'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.member_id' => ['required', 'integer', 'exists:members,id'],
            'rows.*.amount' => ['nullable', 'numeric', 'min:0'],
            'rows.*.notes' => ['nullable', 'string', 'max:1000'],
            'denominations' => ['required', 'array', 'min:1'],
            'denominations.*.value' => ['required', 'numeric', 'min:0.01'],
            'denominations.*.quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
