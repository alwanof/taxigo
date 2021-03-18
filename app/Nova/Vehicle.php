<?php

namespace App\Nova;

use Ctessier\NovaAdvancedImageField\AdvancedImage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Vehicle extends Resource
{
    public static function label()
    {
        return __('Vehicles');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Vehicle');
    }
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Vehicle::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title'
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
            Avatar::make(__('Avatar'), 'avatar')->onlyOnIndex(),
            AdvancedImage::make(__('Avatar'), 'avatar')->croppable(2 / 1)->resize(125)->disk('public')->path('vehicles')->hideFromIndex(),
            Text::make(__('Title'), 'title')
                ->sortable()
                ->rules('required', 'max:42'),
            Slug::make(__('Slug'), 'slug')
                ->from('Title')
                //->creationRules('unique:vehicles,slug')
                ->creationRules('required', Rule::unique('vehicles', 'slug')->where('user_id', auth()->user()->id))
                ->hideFromIndex()
                ->withMeta(['readonly' => true]),
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
