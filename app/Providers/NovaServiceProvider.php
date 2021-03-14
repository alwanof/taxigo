<?php

namespace App\Providers;

use App\Nova\Metrics\DriverCount;
use App\Nova\Metrics\DriverPartition;
use App\Nova\Metrics\DriverTrend;
use App\Nova\Metrics\OrderCount;
use App\Nova\Metrics\OrdersFee;
use App\Nova\Metrics\OrdersIncome;
use App\Nova\Metrics\OrdersNpricedOfficeFee;
use App\Nova\Metrics\OrdersPricedOfficeFee;
use App\Nova\Metrics\OrderTrend;
use App\Nova\Metrics\UserCount;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Silvanite\NovaToolPermissions\NovaToolPermissions;
use Digitalcloud\MultilingualNova\NovaLanguageTool;
use Illuminate\Support\Facades\Auth;
use Muradalwan\DriversMap\DriversMap;
use Muradalwan\TaxiOrder\TaxiOrder;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        // Gate::define('viewNova', function ($user) {
        //     return in_array($user->email, [
        //         //
        //     ]);
        // });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        $parseKeys = [
            'PARSE_APP_ID' => env('PARSE_APP_ID'),
            'PARSE_JS_KEY' => env('PARSE_JS_KEY'),
            'PARSE_SERVER_LQ_URL' => env('PARSE_SERVER_LQ_URL')
        ];
        $metrics = [];
        $level = Auth::user()->level;
        switch ($level) {
            case 0:
                $metrics = [
                    (new DriversMap)->withMeta(['PARSE' => $parseKeys])->authUser(),
                    new UserCount(),
                    new DriverCount(),
                    new DriverTrend(),
                    new DriverPartition(),
                    //new OrderCount(),
                    //new OrderTrend(),
                ];
                break;
            case 1:
                $metrics = [
                    (new DriversMap)->withMeta(['PARSE' => $parseKeys])->authUser(),
                    new UserCount(),
                    new DriverCount(),
                    new DriverTrend(),
                    new DriverPartition(),
                    //new OrderCount(),
                    //new OrderTrend(),
                    //new OrdersIncome(),
                    //new OrdersPricedOfficeFee(),
                    //new OrdersNpricedOfficeFee()
                ];
                break;
            case 2:
                $metrics = [
                    (new DriversMap)->withMeta(['PARSE' => $parseKeys])->authUser(),
                    new DriverCount(),
                    new DriverTrend(),
                    new DriverPartition(),
                    //new OrderCount(),
                    //new OrderTrend(),
                    //new OrdersIncome(),
                    //new OrdersPricedOfficeFee(),
                    //new OrdersNpricedOfficeFee()
                ];
                break;
        }
        return $metrics;
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new NovaToolPermissions(),
            new NovaLanguageTool(),
            //(new TaxiOrder)->currentVisitors()
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
