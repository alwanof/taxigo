<?php

namespace App;

use App\Traits\Multitenantable;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use Multitenantable;

    protected $fillable = ['key', 'value'];
}
