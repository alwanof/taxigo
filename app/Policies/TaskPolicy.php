<?php

namespace App\Policies;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
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

        return Gate::any(['viewTask', 'manageTask'], $user);
    }

    public function view($user, $task)
    {

        return Gate::any(['viewTask', 'manageTask'], $user, $task);
    }

    public function create($user)
    {
        return $user->can('manageTask');
    }

    public function update($user, $task)
    {
        return $user->can('manageBlog', $task);
    }

    public function delete($user, $task)
    {
        return $user->can('manageTask', $task);
    }

    public function restore($user, $task)
    {
        return $user->can('manageTask', $task);
    }

    public function forceDelete($user, $task)
    {
        return $user->can('manageTask', $task);
    }
}
