<?php

namespace App\Providers;

use App\Policies\CommunicationPreferencePolicy;
use App\Models\Communication\CommunicationPreference;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class CommunicationCenterServiceProvider extends ServiceProvider
{
    protected $policies = [
        CommunicationPreference::class => CommunicationPreferencePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
