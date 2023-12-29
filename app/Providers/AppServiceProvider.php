<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\BlockService;
use App\Services\Contracts\AuthTokenGenerator;
use App\Services\Contracts\BlockModel;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthTokenGenerator::class, AuthService::class);
        $this->app->bind(BlockModel::class, BlockService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
