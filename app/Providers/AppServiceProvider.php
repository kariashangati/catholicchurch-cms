<?php

namespace App\Providers;

use App\Services\Frontend\FrontendContentService;
use Illuminate\Support\Facades\Gate;
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
        | This prevents Laravel from generating insecure http:// form actions,
        | redirects, links, and asset URLs while deployed behind Railway.
        |
        */
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
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