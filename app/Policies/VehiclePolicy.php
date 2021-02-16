<?php

namespace App\Policies;

use App\User;
use App\Vehicle;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return Gate::any(['viewVehicle'], $user);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\Vehicle  $vehicle
     * @return mixed
     */
    public function view(User $user, Vehicle $vehicle)
    {
        return Gate::any(['viewVehicle', 'manageVehicle'], $user, $vehicle);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return Gate::any(['manageVehicle'], $user);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\Vehicle  $vehicle
     * @return mixed
     */
    public function update(User $user, Vehicle $vehicle)
    {
        return Gate::any(['manageVehicle'], $user, $vehicle);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Vehicle  $vehicle
     * @return mixed
     */
    public function delete(User $user, Vehicle $vehicle)
    {
        return Gate::any(['manageVehicle'], $user, $vehicle);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\Vehicle  $vehicle
     * @return mixed
     */
    public function restore(User $user, Vehicle $vehicle)
    {
        return Gate::any(['manageVehicle'], $user, $vehicle);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\Vehicle  $vehicle
     * @return mixed
     */
    public function forceDelete(User $user, Vehicle $vehicle)
    {
        return Gate::any(['manageVehicle'], $user, $vehicle);
    }
}
