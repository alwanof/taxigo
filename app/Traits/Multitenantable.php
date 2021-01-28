<?php

namespace App\Traits;

use App\Task;
use App\User;
use Illuminate\Database\Eloquent\Builder;

trait Multitenantable
{


    protected static function bootMultitenantable()
    {


        if (auth()->check()) {

            static::creating(function ($model) {
                try {
                    $table = $model->getTable();
                    if ($table == 'users') {

                        $model->ref = User::withoutGlobalScope('ref')->find(auth()->user()->id)->id;
                        switch ($model->level) {
                            case 0:
                                $model->roles()->sync([1]);

                                break;
                            case 1:
                                $model->roles()->sync([2]);

                                break;
                            case 2:
                                $model->roles()->sync([3]);
                                break;
                        }
                    } else {

                        $model->user_id = auth()->user()->id;
                        $model->parent = auth()->user()->parent->id;
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });

            static::created(function ($model) {
                try {
                    $table = $model->getTable();
                    if ($table == 'users') {
                        switch ($model->level) {
                            case 0:
                                $model->roles()->sync([1]);
                                break;
                            case 1:
                                $model->roles()->sync([2]);
                                break;
                            case 2:
                                $model->roles()->sync([3]);
                                break;
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });

            /*static::updating(function ($model) {
                try {
                    $table = $model->getTable();
                    if ($table == 'users') {
                        $model->ref = auth()->id();
                    } else {
                        $model->user_id = auth()->id();
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });*/

            static::addGlobalScope('ref', function (Builder $builder) {

                try {
                    $table = ($builder->getModels()[0])->table;
                    $level = auth()->user()->level;
                    if ($table == 'users') {
                        switch ($level) {
                            case 1:
                                $builder->where('ref', auth()->id())->orWhere('id', auth()->id());

                                break;
                            case 2:
                                $builder->where('id', auth()->id());

                                break;
                        }
                    } else {
                        switch ($level) {
                            case 1:
                                $builder->where('parent', auth()->id());
                                break;
                            case 2:
                                $builder->where('user_id', auth()->id());
                                break;
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }
            });

            static::addGlobalScope('active', function (Builder $builder) {
                if (auth()->guest()) {
                    $builder->where('active', 1);
                }
            });
        }
    }
}
