<?php

namespace App;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use Multitenantable;

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }
}
