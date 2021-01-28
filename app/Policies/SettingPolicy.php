<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class SettingPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny($user)
    {

        return Gate::any(['manageSetting'], $user);
    }

    public function view($user, $setting)
    {

        return Gate::any(['viewTask', 'manageTask'], $user, $setting);
    }

    public function create($user)
    {
        return $user->can('manageTask');
    }

    public function update($user, $setting)
    {
        return $user->can('manageSetting', $setting);
    }

    public function delete($user, $setting)
    {
        return $user->can('manageSetting', $setting);
    }

    public function restore($user, $setting)
    {
        return $user->can('manageSetting', $setting);
    }

    public function forceDelete($user, $setting)
    {
        return $user->can('manageSetting', $setting);
    }
}
