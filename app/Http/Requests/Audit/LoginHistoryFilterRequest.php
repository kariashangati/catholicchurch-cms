<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginHistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit.logins.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['success', 'failed', 'logged_out', 'locked_out'])],
            'risk_level' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'suspicious_only' => ['nullable', 'boolean'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ];
    }
}