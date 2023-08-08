<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $developer = Role::where('slug','web-developer')->first();
        // $manager = Role::where('slug', 'project-manager')->first();
        // $createTasks = Permission::where('slug','create-tasks')->first();
        // $manageUsers = Permission::where('slug','manage-users')->first();
        // $user1 = new Users();
        // $user1->name = 'Jhon Deo';
        // $user1->email = 'jhon@deo.com';
        // $user1->password = bcrypt('secret');
        // $user1->save();
        // $user1->roles()->attach($developer);
        // $user1->permissions()->attach($createTasks);
        // $user2 = new Users();
        // $user2->name = 'Mike Thomas';
        // $user2->email = 'mike@thomas.com';
        // $user2->password = bcrypt('secret');
        // $user2->save();
        // $user2->roles()->attach($manager);
        // $user2->permissions()->attach($manageUsers);

        $user = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@lms.com',
                'password' => Hash::make('password')
            ]
            );
            $user->assignRole('admin');
    }
}
