<?php

namespace App\Observers;

use App\Driver;
use App\Parse\User;
use Illuminate\Support\Facades\Auth;

class DriverObserver
{
    /**
     * Handle the driver "created" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function created(Driver $driver)
    {
        $user = User::create(['username' => $driver->email, 'password' => $driver->password, 'name' => $driver->name]);
        if (isset($user->username)) {
            $driver->hash = $user->id;
            $driver->save();
        }
    }

    /**
     * Handle the driver "updated" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function updated(Driver $driver)
    {
        $dMan = User::findOrFail($driver->hash);
        $dMan->useMasterKey(true);
        if ($dMan) {
            $dMan->name = $driver->name;
            $dMan->password = $driver->password;
            $dMan->save();
        }
    }

    /**
     * Handle the driver "deleted" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function deleted(Driver $driver)
    {
        $dMan = User::findOrFail($driver->hash);
        $dMan->useMasterKey(true);
        if ($dMan) {
            $dMan->delete();
        }
    }

    /**
     * Handle the driver "restored" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function restored(Driver $driver)
    {
        //
    }

    /**
     * Handle the driver "force deleted" event.
     *
     * @param  \App\Driver  $driver
     * @return void
     */
    public function forceDeleted(Driver $driver)
    {
        //
    }
}
