<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Middleware\CheckUserRole;
use App\Models\Course;
use App\Models\User;
use App\Models\UserSkills;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //All Users
        $users = User::all();
        // Return Json Response
        return response()->json([
            'users' => $users
        ], 200);
    }
    //! Search students
    public function search(Request $request)
    {
        $name = $request->input('name');

        if (empty($name)) {
            return response()->json(['error' => 'Параметр "name" отсутствует или пуст.'], 400);
        }

        $name = strtolower($name); // Приводим значение к нижнему регистру

        $courses = User::whereRaw('LOWER(`name`) LIKE ?', ['%' . $name . '%'])
            ->whereNull('deleted_at')
            ->get();

        if ($courses->isEmpty()) {
            return response()->json(['message' => 'Нет результатов для вашего запроса.'], 200);
        }

        return response()->json(['courses' => $courses], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //User detail
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        // Return Json Response
        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            //find user
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found!!'
                ], 404);
            }
            $data = [
                $user->name = $request->name,
                $user->email = $request->email,
                $user->phone = $request->phone,
            ];
            $user->save($data);
            //Return Json Response
            return response()->json([
                'message' => "user succefully updated."
            ], 200);
        } catch (\Exception $e) {
            //Return Json Response
            return response()->json([
                'message' => $e,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        $user->delete();
        return response()->json([
            'message' => "User succefully deleted."
        ], 200);
    }



    public function updateUserRole(Request $request, $userId, $roleId)
    {
        $user = User::findOrFail($userId);
        $role = UserType::findOrFail($roleId);
        $adminUser = Auth::user();

        if (!$adminUser->hasRole(UserType::Admin)) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Проверяем, является ли переданный ID роли допустимым
        if (!in_array($role, UserType::getValues())) {
            return response()->json(['error' => 'Invalid role ID. Valid role IDs are: ' . implode(', ', UserType::getValues())], 422);
        }

        // Remove existing roles before assigning the new one
        $user->roles()->detach();

        // Assign the new role
        $user->assignRole($roleId);

        return response()->json(['message' => 'User role updated successfully.']);
    }
}
