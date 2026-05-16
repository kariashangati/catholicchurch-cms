<?php

namespace App\Services\SystemConfig;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

class SystemInformationService
{
    public function data(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => Application::VERSION,
            'app_env' => config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'app_url' => config('app.url'),
            'app_locale' => config('app.locale'),
            'app_fallback_locale' => config('app.fallback_locale'),

            'db_connection' => config('database.default'),
            'db_host' => config('database.connections.' . config('database.default') . '.host'),
            'db_port' => config('database.connections.' . config('database.default') . '.port'),
            'db_database' => config('database.connections.' . config('database.default') . '.database'),

            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'cache_store' => config('cache.default'),
            'filesystem_disk' => config('filesystems.default'),

            'server_time' => now()->toDateTimeString(),
            'database_status' => $this->databaseStatus(),
        ];
    }

    protected function databaseStatus(): string
    {
        try {
            DB::connection()->getPdo();

            return 'connected';
        } catch (\Throwable $e) {
            return 'disconnected';
        }
    }
}