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
     
            Role::create(['name' => UserType::Admin]);
            Role::create(['name' => UserType::Teacher]);
            Role::create(['name' => UserType::Student]);
        
    }
    public function down()
    {
        Role::whereIn('name', [UserType::Admin, UserType::Teacher, UserType::Student])->delete();
    }
}
