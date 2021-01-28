<?php

namespace App\Providers;

use App\Driver;
use App\Observers\DriverObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\UserObserver;
use App\User;
use Laravel\Nova\Nova;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function () {
            Driver::observe(DriverObserver::class);
        });
        //User::observe(UserObserver::class);
        //Driver::observe(DriverObserver::class);
    }
}
