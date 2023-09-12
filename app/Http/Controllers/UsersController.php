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
    public function __construct()
    {
        $this->middleware(CheckUserRole::class);
    }
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'phone' => 'required|string'
        ]);
        // dd()->response()->json($request);
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

    public function assignRoleToUser($userId, $userType)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($userType === UserType::Admin) {
            $roleName = UserType::Admin;
        } elseif ($userType === UserType::Teacher) {
            $roleName = UserType::Teacher;
        } elseif ($userType === UserType::Student) {
            $roleName = UserType::Student;
        } else {
            return response()->json(['error' => 'Invalid user type'], 400);
        }

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned successfully']);
    }



    public function showUserRole(User $user)
    {
        if ($user) {
            $role = $user->roles->first();

            if ($role) {
                return response()->json(['role' => $role->name]);
            } else {
                return response()->json(['error' => 'User has no role'], 404);
            }
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

   
}
