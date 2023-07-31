<?php

namespace App\Providers;

use Illuminate\Foundation\Auth\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        
        // $this->registerPolicies();

        // Define roles and permissions
        // $adminRole = Role::create(['name' => 'admin']);
        // $userRole = Role::create(['name' => 'user']);

        // Permission::create(['name' => 'manage_users']);
        // Permission::create(['name' => 'manage_roles']);

        // Assign permissions to roles
        // $adminRole->givePermissionTo('manage_users', 'manage_roles');


        // Assign roles to users (example)
        // $user = User::find(1);
        // $user->assignRole('admin');

    }
}
