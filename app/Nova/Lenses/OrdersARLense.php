<?php

namespace App\Nova\Lenses;

use App\Nova\Filters\RangeOrderFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class OrdersARLense extends Lens
{
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
        $level = Auth::user()->level;
        if ($level == 0) {
            return $request->withOrdering($request->withFilters(
                $query->withoutGlobalScope('ref')->from(DB::raw(
                    "(SELECT users.id 'ID',users.name 'name',COUNT(orders.id) 'orders',SUM(orders.offer) pricedincome,!ISNULL(orders.offer) 'type' FROM users INNER JOIN orders on users.id=orders.user_id GROUP by users.id,users.name,Type) users"
                ))->select('ID', 'name', 'orders', 'type', 'pricedincome')

            ));
        } else {
            $currency = Auth::user()->settings['currency'];
            $pricedFactor = Auth::user()->settings['p_order_fee'] / 100;
            $factor = Auth::user()->settings['order_fee'];
            return $request->withOrdering($request->withFilters(
                $query->withoutGlobalScope('ref')->from(DB::raw(
                    "(SELECT users.id 'ID',users.name 'name',COUNT(orders.id) 'orders',SUM(orders.offer) pricedincome,ROUND(SUM(orders.offer)*$pricedFactor,2) pricedfee,COUNT(orders.id)*$factor nonpricedfee,!ISNULL(orders.offer) 'type' FROM users INNER JOIN orders on users.id=orders.user_id where users.ref=$id GROUP by users.id,users.name,Type) users"
                ))->select('ID', 'name', 'orders', 'type', 'pricedincome', 'pricedfee', 'nonpricedfee')

            ));
        }
    }

    /**
     * Get the fields available to the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $id = Auth::ID();
        $level = Auth::user()->level;
        if ($level == 0) {
            return [
                ID::make(__('ID'), 'id')->sortable(),
                Text::make(__('Name'), 'name')->sortable(),
                Text::make(__('Orders'), 'orders')->sortable(),
                Text::make(__('Income'), 'pricedincome')->sortable(),
                Boolean::make(__('Priced'), 'type')->sortable(),

            ];
        } else {
            return [
                ID::make(__('ID'), 'id')->sortable(),
                Text::make(__('Name'), 'name')->sortable(),
                Text::make(__('Orders'), 'orders')->sortable(),
                Text::make(__('Income'), 'pricedincome'),
                Text::make(__('Priced Fee'), 'pricedfee')->sortable(),
                Text::make(__('NON Priced Fee'), 'nonpricedfee')->sortable(),
                Boolean::make(__('Priced'), 'type')->sortable(),

            ];
        }
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
        return 'orders-a-r-lense';
    }
}
