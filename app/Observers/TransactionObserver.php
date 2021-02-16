<?php

namespace App\Observers;

use App\Driver;
use App\Transaction;
use App\Parse\User;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    /**
     * Handle the transaction "created" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
        $driver = Driver::findOrFail($transaction->driver_id);
        $driver->balance += $transaction->amount;
        $driver->save();
    }

    /**
     * Handle the transaction "updated" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function updated(Transaction $transaction)
    {
    }

    /**
     * Handle the transaction "deleted" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function deleted(Transaction $transaction)
    {
        $driver = Driver::findOrFail($transaction->driver_id);
        $driver->balance -= $transaction->amount;
        $driver->save();
    }

    /**
     * Handle the transaction "restored" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function restored(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the transaction "force deleted" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction)
    {
        //
    }
}
