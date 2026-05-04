<?php

namespace App\Providers;

use App\Models\MonitoredWebsite;
use App\Observers\MonitoredWebsiteObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        MonitoredWebsite::observe(MonitoredWebsiteObserver::class);
    }
}
