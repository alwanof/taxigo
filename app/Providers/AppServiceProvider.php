<?php

namespace App\Providers;

use App\Driver;
use App\Observers\DriverObserver;
use App\Observers\OrderObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\UserObserver;
use App\Order;
use App\Transaction;
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
            Transaction::observe(TransactionObserver::class);
            Order::observe(OrderObserver::class);
        });
        //User::observe(UserObserver::class);
        //Driver::observe(DriverObserver::class);
    }
}
