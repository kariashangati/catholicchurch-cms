<?php

namespace App\Providers;

use App\Services\Frontend\FrontendContentService;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Force HTTPS URL Generation in Production
        |--------------------------------------------------------------------------
        |
        | Railway serves the public app over HTTPS. This prevents Laravel from
        | generating insecure http:// links, redirects, and form actions.
        |
        */
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        /*
        |--------------------------------------------------------------------------
        | Log Slow Database Requests in Production
        |--------------------------------------------------------------------------
        |
        | If a single request spends more than 3 seconds in database queries,
        | write a warning into Railway logs. This helps us identify the exact
        | page and SQL query causing slowness or 500 timeout errors.
        |
        */
        if ($this->app->environment('production')) {
            DB::whenQueryingForLongerThan(
                3000,
                function (Connection $connection, QueryExecuted $event): void {
                    Log::warning('SLOW_DATABASE_REQUEST', [
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'connection' => $connection->getName(),
                        'sql' => $event->sql,
                        'bindings' => $event->bindings,
                        'query_time_ms' => $event->time,
                    ]);
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Global Authorization
        |--------------------------------------------------------------------------
        */
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        /*
        |--------------------------------------------------------------------------
        | Shared Frontend Content
        |--------------------------------------------------------------------------
        */
        View::composer('frontend.*', function ($view) {
            $frontend = app(FrontendContentService::class);

            $view->with($frontend->shared());
        });
    }
}