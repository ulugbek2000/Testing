<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Permission;
// use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@lms.com',
                'password' => Hash::make('password')
            ]
        );
        $user->assignRole(UserType::Admin);

        Role::create(['name' => 'admin']);

        $adminRole = Role::create(['name' => 'admin']);

        $user = User::find(1); // Здесь 1 - ID пользователя
        if ($user) {
            $user->assignRole($adminRole);
        }
    }
}
