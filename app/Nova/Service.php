<?php

namespace App\Nova;

use App\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Service extends Resource
{
    public static function label()
    {
        return __('Services');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Service');
    }
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Service::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'plan';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'plan'
    ];

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
            Text::make(__('Title'), 'title')
                ->sortable()
                ->rules('required', 'max:42'),
            Select::make(__('Plan'), 'plan')->options(function () {
                return [
                    'NONE' => 'NONE',
                    'OFFER' => 'OFFER',
                    'TRACK' => 'TRACK',
                    'DRIVER' => 'DRIVER',
                ];
            })->rules('required'),
            Number::make(__('Const'), 'const')
                ->rules('required', 'min:0')->step(0.1)->default(0),
            Number::make(__('Distance'), 'distance')
                ->rules('required', 'min:0')->step(0.1)->default(0),
            Number::make(__('Time'), 'time')->step(0.01)
                ->rules('required', 'min:0')->default(0),
            Select::make(__('Vehicle'), 'vehicle_id')->options(function () {
                $options = [];
                $vehicles = Vehicle::withoutGlobalScope('ref')
                    ->where('user_id', auth()->user()->id)
                    ->orWhere('user_id', auth()->user()->ref)
                    ->get();
                foreach ($vehicles as $vehicle) {
                    $options[$vehicle->id] = $vehicle->title;
                }
                return $options;
            })->onlyOnForms()
                ->creationRules('required', Rule::unique('services', 'vehicle_id')->where('user_id', auth()->user()->id)),
            //Text::make(__('Queue_Title'), 'qtitle'),
            Boolean::make(__('Queue_Active'), 'qactive'),
            Boolean::make(__('Active'), 'active')->withMeta(["value" => 1]),
            Text::make(__('Vehicle'), 'vehicle_id', function () {
                return Vehicle::withoutGlobalScope('ref')->find($this->vehicle_id)->title;
            })->sortable()->onlyOnIndex(),
            BelongsToMany::make(__('Drivers'))->hideWhenCreating()



        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('user_id', auth()->user()->id);
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
}
