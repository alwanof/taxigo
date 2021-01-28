<?php

namespace App;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Driver extends Model
{
    use Multitenantable;
    use Notifiable;


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
