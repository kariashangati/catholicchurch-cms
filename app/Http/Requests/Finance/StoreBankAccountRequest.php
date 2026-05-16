<?php

namespace App\Http\Requests\Finance;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100', 'unique:bank_accounts,account_number'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(BankAccount::statuses())],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'account_number' => preg_replace('/\s+/', '', (string) $this->input('account_number')),
            'is_active' => $this->boolean('is_active'),
            'status' => BankAccount::normalizeStatus($this->input('status', BankAccount::STATUS_ACTIVE)),
        ]);
    }
}
