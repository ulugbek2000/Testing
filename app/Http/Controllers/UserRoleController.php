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

    public function updateUserRole($userId, $newRole)
    {
        $user = User::findOrFail($userId);

        $adminUser = Auth::user();

        if (!in_array($newRole, UserType::getValues())) {
            return response()->json(['error' => 'Invalid role.'], 422);
        }

        $user->roles()->detach();

        $user->assignRole($newRole);

        return response()->json(['message' => 'User role updated successfully.']);
    }
}
