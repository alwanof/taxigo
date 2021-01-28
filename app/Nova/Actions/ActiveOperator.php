<?php

namespace App\Nova\Actions;


use App\Parse\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ActiveOperator extends Action
{
    use InteractsWithQueue, Queueable;
    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('De/Activate');
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

            if (strlen($model->hash) > 4) {
                $user = User::findOrFail($model->hash);
                $user->useMasterKey(true);
                if ($user->delete()) {
                    //$model->active = 0;
                    $model->hash = '';
                    $model->save();
                }
            } else {
                $user = User::create(['username' => $model->email, 'password' => $model->password, 'email' => $model->email]);
                if (isset($user->username)) {
                    //$model->active = 1;
                    $model->hash = $user->id;
                    $model->save();
                }
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
