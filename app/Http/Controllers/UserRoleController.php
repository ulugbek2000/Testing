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
            $per_page = $request->input('per_page', 12);

            $users = User::paginate($per_page);
            $userCollection = UserResource::collection($users);

            $transformedUsers = $userCollection->map(function ($user) {
                $role = $user->roles->first();
                return [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'surname' => $user['surname'],
                    'phone' => $user['phone'],
                    'role' => $role ? $role->id : null,
                ];
            });

            return $transformedUsers;
        }
    }

    public function updateUserRole(Request $request, User $user, $roleId)
    {
        $adminUser = Auth::user();
        if (!$adminUser->hasRole(UserType::Admin)) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }
    
        // Проверяем, является ли переданный ID роли допустимым
        if (!in_array($roleId, UserType::getValues())) {
            return response()->json(['error' => 'Invalid role id'] ,422);
        }
    
        // Remove existing roles before assigning the new one
        $user->roles()->detach();
    
        // Assign the new role
        $user->assignRole($roleId);
    
        return response()->json(['message' => 'User role updated successfully.']);
    }
}
