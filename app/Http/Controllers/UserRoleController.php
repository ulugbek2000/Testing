<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    public function getAllUsers(Request $request)
    {
        $user = Auth::user();
    
        if ($user->hasRole(UserType::Admin)) {
            $per_page = $request->per_page ?? 12;
    
            $users = User::paginate($per_page);
    
            foreach ($users as $user) {
                $role = $user->roles()->first(); // Получение первой роли пользователя
                $user->role = $role->name; // Предположим, что имя роли находится в поле "name"
            }
    
            return UserResource::collection($users);
        }
    }
    
}
