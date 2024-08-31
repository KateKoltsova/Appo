<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Gallery;
use App\Models\Order;
use App\Models\Price;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\CartPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PricePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Appointment::class => AppointmentPolicy::class,
        Cart::class => CartPolicy::class,
        Gallery::class => GalleryPolicy::class,
        Order::class => OrderPolicy::class,
        Price::class => PricePolicy::class,
        Schedule::class => SchedulePolicy::class,
        Service::class => ServicePolicy::class,
        User::class => UserPolicy::class
    ];

    protected $scopes = [
        'master' => 'Can CRUD: own profile, prices, schedules, appointments, R: services, other profiles, prices, schedules, appointments',
        'client' => 'Can CRUD: own profile, appointments, R: services, other prices, schedules, appointments',
        'admin' => 'Can CRUD: all'
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(config('passport.tokens_lifetime.access_token'));
        Passport::refreshTokensExpireIn(config('passport.tokens_lifetime.refresh_token'));

//        Passport::tokensCan($this->scopes);
    }
}
