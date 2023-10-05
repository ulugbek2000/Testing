<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
     
            Role::create(['name' => UserType::Admin, 'guard_name' => config('auth.defaults.guard')]);
            Role::create(['name' => UserType::Teacher, 'guard_name' => config('auth.defaults.guard')]);
            Role::create(['name' => UserType::Student, 'guard_name' => config('auth.defaults.guard')]);
        
    }
}
