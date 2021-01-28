<?php

namespace App;

use App\Traits\Multitenantable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Silvanite\Brandenburg\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use Multitenantable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'avatar', 'email', 'password', 'level', 'ref', 'active'
    ];

    protected $appends = ['settings'];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($user) {
    //         $user->ref = auth()->user()->id;
    //     });
    // }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getParentAttribute()
    {

        return User::withoutGlobalScope('ref')->find($this->ref);
    }

    public function config($key)
    {
        $officeConfig = Preference::where([
            'key' => $key,
            'user_id' => $this->id
        ])->get();
        if ($officeConfig->count() > 0) {
            return $officeConfig->first()->value;
        }

        $agentConfig = Preference::where([
            'key' => $key,
            'user_id' => $this->parent
        ])->get();
        if ($agentConfig->count() > 0) {
            return $agentConfig->first()->value;
        }

        $config = Setting::where([
            'key' => $key
        ])->get();

        if ($config->count() > 0) {
            return $config->first()->value;
        }

        return 'false';
    }

    public function getSettingsAttribute()
    {

        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {

            $result[$setting->key] = $setting->value;
            $parentConfig = Preference::withoutGlobalScope('ref')->where([
                'key' => $setting->key,
                'user_id' => $this->parent->id
            ])->get();
            if ($parentConfig->count() > 0) {

                $result[$parentConfig->first()->key] = $parentConfig->first()->value;
            }

            $userConfig = Preference::withoutGlobalScope('ref')->where([
                'key' => $setting->key,
                'user_id' => $this->id
            ])->get();


            if ($userConfig->count() > 0) {

                $result[$userConfig->first()->key] = $userConfig->first()->value;
            }
        }

        return $result;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'ref');
    }

    public function main()
    {
        return $this->belongsTo(User::class, 'ref');
    }

    public function userCan($model)
    {
        if ($model->id == $this->id) {
            return true;
        }

        if ($this->level == 0) {
            return true;
        }
        if ($this->level == 1) {
            if ($model->ref == $this->id) {
                return true;
            }
        }
        return false;
    }
}
