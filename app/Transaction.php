<?php

namespace App;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use Multitenantable;
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
