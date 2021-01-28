<?php

namespace App\Nova\Metrics;

use App\Nova\Filters\RangeOrderFilter;
use App\Order;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Nova;
use Nemrutco\Filterable\FilterableValue;

class OrdersNpricedOfficeFee extends Value
{
    use FilterableValue;
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function name()
    {
        return __('NON Priced Fee');
    }

    public function calculate(NovaRequest $request)
    {
        $currency = Auth::user()->settings['currency'];
        //$pricedFactor = (Auth::user()->settings['p_order_fee']) / 100;
        $factor = Auth::user()->settings['order_fee'];
        $timezone = Nova::resolveUserTimezone($request) ?? $request->timezone;


        if ($request->input('App\Nova\Filters\RangeOrderFilter')) {
            $rangeDate = explode(" to ", $request->input('App\Nova\Filters\RangeOrderFilter'));
            $res = Order::whereNull('offer')
                ->whereBetween('created_at', $rangeDate)
                ->get()->count();
        } else {
            $res = Order::whereNull('offer')->whereBetween('created_at', $this->currentRange($request->range, $timezone))->get()->count();
        }

        //$prev = Order::whereNull('offer')->whereBetween('created_at', $this->previousRange($request->range, $timezone))->get()->count();

        return $this->result(round($res * $factor, 2))->currency($currency);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            30 => __('30 Days'),
            60 => __('60 Days'),
            365 => __('365 Days'),
            'TODAY' => __('Today'),
            'MTD' => __('Month To Date'),
            'QTD' => __('Quarter To Date'),
            'YTD' => __('Year To Date'),
        ];
    }

    public function filters()
    {
        return [
            (new RangeOrderFilter)->range()
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'orders-npriced-office-fee';
    }
}
