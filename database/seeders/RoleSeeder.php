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
        foreach(UserType::values() as $role){
            Role::create([
                'name' => $role,
                'guard_name' => 'web'
            ]);
        }
    }
}
