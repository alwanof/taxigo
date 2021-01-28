<?php

namespace App\Nova\Lenses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class OrderOfficeLense extends Lens
{
    public function name()
    {
        return __('Driver Orders');
    }
    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  \Laravel\Nova\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public static function query(LensRequest $request, $query)
    {
        $id = Auth::ID();
        return $request->withOrdering($request->withFilters(
            $query->withoutGlobalScope('ref')->from(DB::raw(
                "(SELECT drivers.id 'ID',drivers.name 'name',drivers.phone 'phone',COUNT(orders.id) 'total',!ISNULL(orders.offer) 'type' FROM drivers INNER join orders on drivers.id=orders.driver_id where drivers.user_id=$id GROUP by drivers.id,drivers.name,drivers.phone , Type) drivers"
            ))->select('ID', 'name', 'phone', 'total', 'type')

        ));
    }



    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Name'), 'name')->sortable(),
            Text::make(__('Phone'), 'phone'),
            Text::make(__('Total'), 'total')->sortable(),
            Boolean::make(__('Priced'), 'type')->sortable(),
        ];
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return parent::actions($request);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'order-office-lense';
    }
}
