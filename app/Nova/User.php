<?php

namespace App\Nova;

use App\Driver;
use App\Nova\Actions\TestActionn;
use App\Nova\Lenses\OrdersARLense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Silvanite\NovaToolPermissions\Role;
use Ctessier\NovaAdvancedImageField\AdvancedImage;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

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
        'name', 'email',
    ];
    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Users');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('User');
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

            //Avatar::make('Avatar')->squared()->disk('public'),
            Avatar::make(__('Avatar'), 'avatar')->onlyOnIndex(),
            AdvancedImage::make(__('Avatar'), 'avatar')->croppable(1 / 1)->resize(320)->disk('public')->path('users')->hideFromIndex(),

            Text::make(__('Name'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make(__('Email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make(__('Password'), 'password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),

            Text::make(__('Level'), 'level', function () {
                switch ($this->level) {
                    case 0:
                        return __('Root');
                        break;
                    case 1:
                        return __('Agent');
                        break;
                    case 2:
                        return __('Offcie');
                        break;

                    default:
                        return '#NA';
                        break;
                }
            })->onlyOnIndex(),
            Select::make(__('Level'), 'level')->options(function () {
                $options = [];
                $level = auth()->user()->level;
                if ($level == 0) {
                    $options[0] = __('Root');
                    $options[1] = __('Agent');
                }
                if ($level == 1) {
                    $options[2] = __('Offcie');
                }
                return $options;
            })->creationRules('required')->onlyOnForms(),


            Boolean::make(__('Active'), 'active')->onlyOnDetail()->onlyOnForms()->withMeta(["value" => 1]),
            /*Text::make(__('Ref'), 'ref', function () {

                return ($this->parent) ? $this->parent->name : '-';
            })->onlyOnIndex(),*/
            BelongsTo::make(__('Ref'), 'main', User::class)->hideWhenCreating()->hideWhenUpdating(),
            BelongsToMany::make(__('Roles'), 'roles', Role::class),
            HasMany::make(__('Orders'), 'orders', Order::class),
            HasMany::make(__('Children'), 'children', User::class)->hideWhenCreating()->hideWhenUpdating()

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
        return [
            new OrdersARLense()
        ];
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
            //(new TestActionn())->showOnTableRow(),
        ];
    }
}
