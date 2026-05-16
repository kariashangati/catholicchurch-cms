<?php

namespace App\Services\SystemConfig;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;

class EnvironmentSettingsService
{
    protected array $editableKeys = [
        'APP_NAME',
        'APP_ENV',
        'APP_DEBUG',
        'APP_URL',
        'APP_LOCALE',
        'APP_FALLBACK_LOCALE',

        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',

        'SESSION_DRIVER',
        'SESSION_LIFETIME',
        'QUEUE_CONNECTION',
        'CACHE_STORE',
        'FILESYSTEM_DISK',

        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',

        'BONGO_LIVE_KEY',
        'BONGO_LIVE_SECRET',
        'BONGO_SENDER_ID',
        'BEEM_SMS_SEND_URL',
        'BEEM_SMS_BALANCE_URL',
    ];

    protected array $maskedKeys = [
        'APP_KEY',
        'DB_PASSWORD',
        'MAIL_PASSWORD',
        'BONGO_LIVE_SECRET',
        'BONGO_LIVE_KEY',
    ];

    public function all(): array
    {
        $path = $this->envPath();
        $lines = File::exists($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];

        $data = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $data[trim($key)] = $this->trimEnvValue($value);
        }

        return $data;
    }

    public function grouped(): array
    {
        $env = $this->all();

        return [
            'app' => $this->collectKeys($env, [
                'APP_NAME',
                'APP_ENV',
                'APP_DEBUG',
                'APP_URL',
                'APP_LOCALE',
                'APP_FALLBACK_LOCALE',
            ]),
            'database' => $this->collectKeys($env, [
                'DB_CONNECTION',
                'DB_HOST',
                'DB_PORT',
                'DB_DATABASE',
                'DB_USERNAME',
                'DB_PASSWORD',
            ]),
            'session_cache_queue' => $this->collectKeys($env, [
                'SESSION_DRIVER',
                'SESSION_LIFETIME',
                'QUEUE_CONNECTION',
                'CACHE_STORE',
                'FILESYSTEM_DISK',
            ]),
            'mail' => $this->collectKeys($env, [
                'MAIL_MAILER',
                'MAIL_HOST',
                'MAIL_PORT',
                'MAIL_USERNAME',
                'MAIL_PASSWORD',
                'MAIL_FROM_ADDRESS',
                'MAIL_FROM_NAME',
            ]),
            'sms' => $this->collectKeys($env, [
                'BONGO_LIVE_KEY',
                'BONGO_LIVE_SECRET',
                'BONGO_SENDER_ID',
                'BEEM_SMS_SEND_URL',
                'BEEM_SMS_BALANCE_URL',
            ]),
        ];
    }

    public function editableKeys(): array
    {
        return $this->editableKeys;
    }

    public function maskedKeys(): array
    {
        return $this->maskedKeys;
    }

    public function updateMany(array $items): void
    {
        foreach ($items as $key => $value) {
            if (! in_array($key, $this->editableKeys, true)) {
                continue;
            }

            $this->set($key, $value);
        }
    }

    public function set(string $key, mixed $value): void
    {
        if (! in_array($key, $this->editableKeys, true)) {
            throw new RuntimeException("Key [{$key}] is not editable.");
        }

        $path = $this->envPath();

        if (! File::exists($path)) {
            throw new RuntimeException('.env file not found.');
        }

        $envContent = File::get($path);
        $formattedValue = $this->formatEnvValue($value);

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $envContent)) {
            $envContent = preg_replace($pattern, "{$key}={$formattedValue}", $envContent);
        } else {
            $envContent .= PHP_EOL . "{$key}={$formattedValue}";
        }

        File::put($path, $envContent);
    }

    public function displayValue(string $key, mixed $value): mixed
    {
        if (in_array($key, $this->maskedKeys, true) && filled($value)) {
            return str_repeat('*', 8);
        }

        return $value;
    }

    protected function collectKeys(array $env, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = [
                'value' => $env[$key] ?? null,
                'display_value' => $this->displayValue($key, $env[$key] ?? null),
                'is_editable' => in_array($key, $this->editableKeys, true),
                'is_masked' => in_array($key, $this->maskedKeys, true),
            ];
        }

        return $result;
    }

    protected function formatEnvValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $string = (string) $value;

        if (preg_match('/\s/', $string)) {
            return '"' . addslashes($string) . '"';
        }

        return $string;
    }

    protected function trimEnvValue(string $value): string
    {
        return trim($value, "\"'");
    }

    protected function envPath(): string
    {
        return app()->environmentFilePath();
    }
}