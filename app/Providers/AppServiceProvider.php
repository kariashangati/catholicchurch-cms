<?php

namespace App\Providers;

use App\Services\Frontend\FrontendContentService;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        View::composer('frontend.*', function ($view) {
            $frontend = app(FrontendContentService::class);

            $view->with($frontend->shared());
        });
    }
}