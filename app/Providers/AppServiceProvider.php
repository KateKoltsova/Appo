<?php

namespace App\Providers;

use App\Repositories\AppointmentRepository;
use App\Repositories\CartRepository;
use App\Repositories\PriceRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\UserRepository;
use App\Services\Api\AppointmentService;
use App\Services\Api\CartService;
use App\Services\Api\PriceService;
use App\Services\Api\ScheduleService;
use App\Services\Api\UserService;
use App\Services\AuthService;
use App\Services\BlockService;
use App\Services\Contracts\AuthTokenGenerator;
use App\Services\Contracts\BlockModel;
use App\Services\Contracts\PayService;
use App\Services\ImageService;
use App\Services\LiqpayService;
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
        $this->app->bind(PayService::class, LiqpayService::class);
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService(
                $app->make(BlockModel::class),
                $app->make(PayService::class),
                $app->make(CartRepository::class),
                $app->make(ScheduleService::class)
            );
        });
        $this->app->singleton(AppointmentService::class, function ($app) {
            return new AppointmentService(
                $app->make(BlockModel::class),
                $app->make(AppointmentRepository::class)
            );
        });
        $this->app->singleton(ScheduleService::class, function ($app) {
            return new ScheduleService(
                $app->make(ScheduleRepository::class),
            );
        });
        $this->app->singleton(PriceService::class, function ($app) {
            return new PriceService(
                $app->make(PriceRepository::class),
            );
        });
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepository::class),
                $app->make(ImageService::class)
            );
        });
        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService();
        });
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
