<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Silvanite\Brandenburg\Traits\ValidatesPermissions;

class AuthServiceProvider extends ServiceProvider
{
    use ValidatesPermissions;
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        'App\Task' => 'App\Policies\TaskPolicy',
        'App\Setting' => 'App\Policies\SettingPolicy',
        'App\User' => 'App\Policies\UserPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        collect([
            'viewTask',
            'manageTask',
            'viewDriver',
            'manageDriver',
            'manageSetting',
            'manageUser',
            'viewUser'

        ])->each(function ($permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user->hasRoleWithPermission($permission);
            });
        });
        $this->registerPolicies();

        //
    }
}
