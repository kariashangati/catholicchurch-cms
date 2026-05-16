<?php

namespace App\Http\Requests\Finance\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ContributionComplianceFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('finance.reports.compliance.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'contribution_type_id' => ['nullable', 'integer', 'exists:contribution_types,id'],
            'kanda_id' => ['nullable', 'integer', 'exists:kandas,id'],
            'jumuiya_id' => ['nullable', 'integer', 'exists:jumuiyas,id'],
            'payment_status' => ['nullable', 'in:all,paid,partial,unpaid'],
            'source' => ['nullable', 'in:all,cash,bank,mixed'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
