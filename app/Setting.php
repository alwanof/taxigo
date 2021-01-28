<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getValidAttribute()
    {

        return true;
    }
}
