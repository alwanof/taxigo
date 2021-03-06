<?php

namespace App\Nova\Filters;

use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class OrderOfficeFilter extends Filter
{
    public function name()
    {
        return __('Order Office');
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
        $offices = User::where('level', 2)->get();
        $options = [];
        foreach ($offices as $office) {
            $options[$office->name] =  $office->id;
        }


        return $options;
    }
}
