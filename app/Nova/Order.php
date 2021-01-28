<?php

namespace App\Nova;

use App\Driver;
use App\Nova\Filters\OrderAgentFilter;
use App\Nova\Filters\OrderDriverFilter;
use App\Nova\Filters\OrderOfficeFilter;
use App\Nova\Filters\OrderTypeFilter;
use App\Nova\Filters\RangeOrderFilter;
use App\Nova\Filters\ToOrderFilter;
use App\Nova\Lenses\OrderOfficeLense;
use App\Nova\Metrics\OrderCount;
use Bissolli\NovaPhoneField\PhoneNumber;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Muradalwan\DriversMap\DriversMap;
use Muradalwan\OrdersCard\OrdersCard;
use Muradalwan\OrderStream\OrderStream;
use Illuminate\Support\Str;

class Order extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Order::class;
    //public static $polling = true;
    //public static $showPollingToggle = true;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'email', 'phone',
    ];
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Orders');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Order');
    }


    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {

        return [

            ID::make(__('ID'), 'id')->sortable(),
            Hidden::make('Session')->default(Str::random(64)),
            Hidden::make('Email')->default(Str::random(12) . '@random.comx'),
            Hidden::make('from_lat')->default(41.056051),
            Hidden::make('from_lng')->default(28.9760503),
            Hidden::make('to_lat')->default(41.056051),
            Hidden::make('to_lng')->default(28.9760503),
            Hidden::make('Status')->default(1),

            Text::make(__('Name'), 'name')
                ->rules('required', 'max:255'),
            Text::make(__('Email'), 'email')->hideWhenCreating(),
            PhoneNumber::make(__('Phone'), 'phone')
                ->withCustomFormats('+218 (##[#]) ### ####')
                ->withMeta([
                    'extraAttributes' => [
                        'style' => 'direction:ltr !important'
                    ]
                ]),
            Text::make(__('Address'), 'from_address')
                ->rules('required', 'max:255')
                ->onlyOnForms(),
            Text::make(__('Destination'), 'to_address')
                ->creationRules('required_with:offer')
                ->onlyOnForms(),
            Number::make(__('Offer'), 'offer')
                ->creationRules('required_with:to_address'),
            Text::make(__('Note'), 'note')->hideFromIndex(),
            Text::make(__('Status'), 'status', function () {
                return $this->statusLabel($this->status);
            })->hideWhenCreating(),
            BelongsTo::make(__('Driver'), 'driver', 'App\Nova\Driver')->hideWhenCreating(),

            BelongsTo::make(__('Office'), 'office', 'App\Nova\User')->hideWhenCreating(),
            BelongsTo::make(__('Agent'), 'actor', 'App\Nova\User')->hideWhenCreating(),


        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [
            (new OrdersCard)->authUser()->canSee(function ($request) {

                if (auth()->user()->level != 2) {
                    return false;
                }
                return true;
            }),
            (new DriversMap)->authUser()->canSee(function ($request) {

                if (auth()->user()->level != 2) {
                    return false;
                }
                return true;
            }),

        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            //new RangeOrderFilter(),
            (new RangeOrderFilter)->range(),
            //new OrderAgentFilter(),
            //new OrderOfficeFilter(),
            //new OrderTypeFilter(),
            (new OrderDriverFilter)->authUser()->canSee(function ($request) {

                if (auth()->user()->level != 2) {
                    return false;
                }
                return true;
            }),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    private function statusLabel($status)
    {
        $label = '-';
        switch ($status) {
            case 0:
                $label = 'New';
                break;
            case 1:
                $label = 'Accepted';
                break;
            case 2:
                $label = 'Waiting Driver Approve';
                break;
            case 21:
                $label = 'Proccessing..';
                break;
            case 3:
                $label = 'Waiting Customer Approve';
                break;
            case 9:
                $label = 'Done';
                break;
            case 91:
                $label = 'Office Reject';
                break;
            case 92:
                $label = 'Customer Reject';
                break;
            case 93:
                $label = 'No-Resp from Office';
                break;
            case 94:
                $label = 'No-Resp from Customer';
                break;

            default:
                break;
        }
        return $label;
    }
}
