<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function settings()
    {
        return $this->belongsToMany(Setting::class);
    }
}
