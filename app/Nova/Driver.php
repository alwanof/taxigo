<?php

namespace App\Nova;


use App\Nova\Actions\ActiveOperator;
use App\Nova\Actions\SendCredentionalAction;
use App\Nova\Filters\OrderOfficeFilter;
use App\Nova\Lenses\OrderOfficeLense;
use App\Order;
use App\Parse\User as ParseUser;
use App\User;
use Bissolli\NovaPhoneField\PhoneNumber;
use Ctessier\NovaAdvancedImageField\AdvancedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inspheric\Fields\Email;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opanegro\FieldNovaPasswordShowHide\FieldNovaPasswordShowHide;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;

class Driver extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Driver::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    public static $preventFormAbandonment = true;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'taxiNo'
    ];
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Drivers');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Driver');
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
            AdvancedImage::make(__('Avatar'), 'avatar')->croppable(1 / 1)->resize(320)->disk('public')->path('drivers'),
            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),
            Text::make(__('Taxi'), 'taxi')
                ->rules('required', 'max:255'),
            Text::make(__('TaxiNo'), 'taxiNo')
                ->rules('required', 'max:255')
                ->hideFromIndex(),
            Email::make(__('Email'), 'email')
                ->rules('required', 'email', 'max:255')
                ->hideFromIndex()
                ->clickable(),
            Text::make(__('Password'), 'password')
                ->rules('required', 'min:8', 'max:20')
                ->default(Str::random(8))
                ->hideFromIndex(),
            PhoneNumber::make(__('Phone'), 'phone')
                ->rules('required', 'min:6', 'max:20')
                ->withCustomFormats('+218 (##[#]) ### ####')
                ->withMeta([
                    'extraAttributes' => [
                        'style' => 'direction:ltr !important'
                    ]
                ]),
            Text::make(__('TaxiColor'), 'taxiColor')
                ->rules('required', 'max:25')
                ->hideFromIndex(),

            Text::make(__('Busy'), function () {
                return $this->driverStatus();
            })
                ->onlyOnDetail(),
            Boolean::make('Server Status', function () {
                $driver = ParseUser::find($this->hash);
                if ($driver) {
                    return true;
                } else {
                    return false;
                }
            })
                ->onlyOnDetail(),
            Number::make(__('Distance'), 'distance')
                ->onlyOnIndex(),


            /*Boolean::make(__('Active'), 'active')
                ->hideWhenCreating(),*/
            HasMany::make(__('Orders'), 'orders', 'App\Nova\Order')
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
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        $lens = [];
        $level = auth()->user()->level;
        switch ($level) {
            case 2:
                $lens[] = new OrderOfficeLense();
                break;
        }

        return $lens;
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            //(new ActiveOperator())->onlyOnDetail(),
            (new SendCredentionalAction()),
        ];
    }

    public function driverStatus()
    {
        switch ($this->busy) {
            case 0:
                return __('Offline');
                break;
            case 1:
                return __('BusyNow');
                break;
            case 2:
                return __('Free');
                break;

            default:
                __('Offline');
                break;
        }
    }
}
