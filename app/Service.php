<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Multitenantable;

class Service extends Model
{
    use Multitenantable;
    protected $appends = ['vehicle'];

    public function getVehicleAttribute()
    {
        return Vehicle::find($this->vehicle_id);
    }

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


    /*public function queues(){
        return $this->belongsToMany(Driver::class, 'queues', 'service_id', 'driver_id');
    }*/

    public function drivers()
    {
        return $this->belongsToMany(Driver::class, 'queues');
    }
}
