<?php

namespace Database\Seeders;

use App\Enums\UserType;
// use App\Models\Role;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permission::create([
        //     'name' => 'update-user', 'delete-user',
        //     'create-course', 'update-course', 'delete-course',
        //     'create-topic', 'update-topic', 'delete-topic',
        //     'create-lesson','update-lesson','delete-lesson',
        // ]);

        // Permission::create(['name' => 'create-blog-posts']);
        // Permission::create(['name' => 'edit-blog-posts']);
        // Permission::create(['name' => 'delete-blog-posts']);


        Role::create(['name' => UserType::Admin, 'guard_name' => config('auth.defaults.guard')]);
        Role::create(['name' => UserType::Teacher, 'guard_name' => config('auth.defaults.guard')]);
        Role::create(['name' => UserType::Student, 'guard_name' => config('auth.defaults.guard')]);

        // $adminRole->givePermissionTo([
        //     'create-users',
        //     'edit-users',
        //     'delete-users',
        //     'create-blog-posts',
        //     'edit-blog-posts',
        //     'delete-blog-posts',
        // ]);

        // $editorRole->givePermissionTo([
        //     'create-blog-posts',
        //     'edit-blog-posts',
        //     'delete-blog-posts',
        // ]);
    }
}
