<?php

namespace App\Nova\Actions;

use App\Preference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class SetPreference extends Action
{
    use InteractsWithQueue, Queueable;
    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Set Preference');
    }


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */

    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            $user_preference = Preference::withoutGlobalScope('ref')->where('user_id', auth()->user()->id)
                ->where('key', $model->key);
            if ($user_preference->count() > 0) {
                $feed = $user_preference->first();
                $feed->value = $fields->value;
                $feed->save();
            } else {
                Preference::create(['key' => $model->key, 'value' => $fields->value]);
            }
        }

        //return Action::message($fields->value);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Value'),
        ];
    }
}
