<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResource\UserResource as UserResourceUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    public function getUsers(Request $request)
{
    $user = Auth::user();

    // Проверяем, имеет ли пользователь роль администратора
    if ($user->hasRole(UserType::Admin)) {
        $perPage = $request->input('per_page', 12);
        $search = $request->input('search');

        // Получаем пользователей с учетом пагинации
        $query = User::query();
        
        // Применяем фильтрацию, если есть параметр поиска
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('surname', 'like', "%$search%");
            });
        }
        
        $users = $query->paginate($perPage);
        
        // Преобразуем пользователей в нужный формат
        $transformedUsers = $users->map(function ($user) {
            $role = $user->roles->first();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'phone' => $user->phone,
                'role' => $role ? $role->id : null,
            ];
        });

        // Возвращаем ответ в формате JSON с данными пользователей и метаданными
        return response()->json([
            'users' => $transformedUsers,
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
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
            return response()->json(['error' => 'Invalid role id'], 422);
        }

        // Remove existing roles before assigning the new one
        $user->roles()->detach();

        // Assign the new role
        $user->assignRole($roleId);

        return response()->json(['message' => 'User role updated successfully.']);
    }
}
