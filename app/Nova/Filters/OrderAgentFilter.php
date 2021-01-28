<?php

namespace App\Nova\Filters;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class OrderAgentFilter extends Filter
{
    public function name()
    {
        return __('Order Agent');
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
        return $query->where('parent', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $agents = User::where('level', 1)->get();
        $options = [];
        foreach ($agents as $agent) {
            $options[$agent->name] =  $agent->id;
        }


        return $options;
    }
}
