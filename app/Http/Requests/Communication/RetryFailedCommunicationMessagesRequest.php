<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class RetryFailedCommunicationMessagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('communication.retry_failed') ?? false;
    }

    public function rules(): array
    {
        return [
            'only_latest_failures' => ['nullable', 'boolean'],
        ];
    }
}
