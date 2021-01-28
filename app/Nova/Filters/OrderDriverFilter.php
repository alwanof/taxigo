<?php

namespace App\Nova\Filters;

use App\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Filters\Filter;

class OrderDriverFilter extends Filter
{
    public function name()
    {
        return __('Order Driver');
    }
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        //dd($value);
        return $query->where('driver_id', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $drivers = Driver::where('user_id', Auth::id())->get();
        $options = [];
        foreach ($drivers as $driver) {
            $options[$driver->name] =  $driver->id;
        }


        return $options;
    }

    public function authUser()
    {
        return $this->withMeta(['authUser' => auth()->user()]);
    }
}
