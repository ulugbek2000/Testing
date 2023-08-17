<?php

namespace Database\Seeders;

use App\Enums\UserType;
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
        $user = User::create(
            [
                'name' => 'Admin',
                'email' => 'admin@lms.com',
                'password' => Hash::make('password')
            ]
        );
        $user->assignRole(UserType::ADMIN());
    }
}
