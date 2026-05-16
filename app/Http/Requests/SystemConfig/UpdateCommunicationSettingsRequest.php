<?php

namespace App\Http\Requests\SystemConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.config.communication.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'MAIL_MAILER' => ['nullable', 'string', 'max:50'],
            'MAIL_HOST' => ['nullable', 'string', 'max:255'],
            'MAIL_PORT' => ['nullable', 'string', 'max:20'],
            'MAIL_USERNAME' => ['nullable', 'string', 'max:255'],
            'MAIL_PASSWORD' => ['nullable', 'string', 'max:255'],
            'MAIL_FROM_ADDRESS' => ['nullable', 'string', 'max:255'],
            'MAIL_FROM_NAME' => ['nullable', 'string', 'max:255'],

            'BONGO_LIVE_KEY' => ['nullable', 'string', 'max:255'],
            'BONGO_LIVE_SECRET' => ['nullable', 'string', 'max:500'],
            'BONGO_SENDER_ID' => ['nullable', 'string', 'max:100'],
            'BEEM_SMS_SEND_URL' => ['nullable', 'string', 'max:255'],
            'BEEM_SMS_BALANCE_URL' => ['nullable', 'string', 'max:255'],
        ];
    }
}