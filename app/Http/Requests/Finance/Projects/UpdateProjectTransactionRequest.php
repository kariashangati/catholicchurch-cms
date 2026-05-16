<?php

namespace App\Http\Requests\Finance\Projects;

use App\Models\ProjectTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'transaction_type' => ['required', Rule::in(ProjectTransaction::availableTypes())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(ProjectTransaction::availablePaymentMethods())],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'receipt_no' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(ProjectTransaction::availableStatuses())],
        ];
    }
}