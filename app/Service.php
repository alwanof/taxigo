<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Service extends Model
{
    use Multitenantable;

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function office()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
