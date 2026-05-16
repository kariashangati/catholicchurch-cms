<?php

namespace App\Http\Requests\SystemConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnvironmentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.config.environment.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'APP_NAME' => ['nullable', 'string', 'max:255'],
            'APP_ENV' => ['nullable', 'string', 'max:50'],
            'APP_DEBUG' => ['nullable', 'in:true,false,1,0'],
            'APP_URL' => ['nullable', 'string', 'max:255'],
            'APP_LOCALE' => ['nullable', 'string', 'max:20'],
            'APP_FALLBACK_LOCALE' => ['nullable', 'string', 'max:20'],

            'DB_CONNECTION' => ['nullable', 'string', 'max:50'],
            'DB_HOST' => ['nullable', 'string', 'max:255'],
            'DB_PORT' => ['nullable', 'string', 'max:20'],
            'DB_DATABASE' => ['nullable', 'string', 'max:255'],
            'DB_USERNAME' => ['nullable', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string', 'max:255'],

            'SESSION_DRIVER' => ['nullable', 'string', 'max:50'],
            'SESSION_LIFETIME' => ['nullable', 'string', 'max:50'],
            'QUEUE_CONNECTION' => ['nullable', 'string', 'max:50'],
            'CACHE_STORE' => ['nullable', 'string', 'max:50'],
            'FILESYSTEM_DISK' => ['nullable', 'string', 'max:50'],

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