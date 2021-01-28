<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class DriverPolicy
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
        return Gate::any(['viewDriver', 'manageDriver'], $user);
    }

    public function view($user, $driver)
    {
        return Gate::any(['viewDriver', 'manageDriver'], $user, $driver);
    }

    public function create($user)
    {
        return $user->can('manageDriver');
    }

    public function update($user, $driver)
    {
        return $user->can('manageDriver', $driver);
    }

    public function delete($user, $driver)
    {
        return $user->can('manageDriver', $driver);
    }

    public function restore($user, $driver)
    {
        return $user->can('manageDriver', $driver);
    }

    public function forceDelete($user, $driver)
    {
        return $user->can('manageDriver', $driver);
    }
}
