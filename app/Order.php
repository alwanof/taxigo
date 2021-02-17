<?php

namespace App;

use App\Traits\Multitenantable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // block_drivers=null default,
    //


    // 0 new
    //Office: 1=> S D | 12 => Send Offer
    //Driver: 2 => Y/N option | 21 on the way | 22 start trip

    //Customer: 3 => Y/N option
    //Done 9=> done | 91=> RO | 92=>RC | 93=>NoRO | 94=>NoRC 99=>CC | 90 failed

    //emos_murad emos_taxidb
    // ssh root@142.93.174.231
    //#!S_~2-0-2-1/A*M*T%o%t%i%l+!
    // cd /home/2axigo.com/public_html
    //rm -rf storage
    // scp narabana.com.zip root@142.93.174.231:/home/narabana.com/public_html
    //Zoom+9314MU
    use Multitenantable;

    protected $fillable = [
        'session',
        'name',
        'email',
        'phone',
        'from_address',
        'from_lat',
        'from_lng',
        'to_address',
        'to_lat',
        'to_lng',
        'status',
        'user_id',
        'parent',
        'note'
    ];
    protected $appends = ['driver', 'drivers'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*public function parent(){
        return User::find($this->parent);
    }*/

    public function getDriverAttribute()
    {
        $driver = Driver::find($this->driver_id);
        if (is_object($driver)) {
            return $driver;
        }

        return false;
    }

    public function getDriversAttribute()
    {
        $block = explode('--', $this->block);
        $drivers = Driver::where('user_id', $this->user_id)
            ->where('busy', 2)
            ->whereNotIn('id', $block)
            ->get();

        return $drivers;
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function subscribers()
    {
        return $this->belongsToMany(Driver::class, 'driver_order');
    }

    public function office()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function actor()
    {
        return $this->belongsTo(User::class, 'parent');
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d-M-Y H:i:s');
    }

    public function orderTotal($d, $t)
    {
        $orderPrice = 0;
        if ($this->service) {
            if ($this->service->plane == 'TRACK') {
                $orderPrice = (($d / 1000) * $this->service->distance) + (($t / 60) * $this->service->time) + $this->service->const;
            }
        }
        return round($orderPrice, 2);
    }
}
