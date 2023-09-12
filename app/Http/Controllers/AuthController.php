<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSkills;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    public function register(Request $request, User $user)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required_without:phone|email|unique:users',
            'phone' => 'required_without:email|string|unique:users',
            'password' => 'required|string|min:8',
            'city' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('email')) {
            $user = User::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'city' => $request->input('city'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
                'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photo', 'public') : null,
            ]);
        } elseif ($request->has('phone')) {
            $user = User::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
                'city' => $request->input('city'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
                'photo' => $request->hasFile('photo') ? $request->file('photo')->store('photo', 'public') : null,
            ]);
        }

        $user->assignRole(UserType::Student);
        // $user->user_type = 'student';

        // $user->save();
        return response()->json(['message' => 'Registration successful'], 201);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email_or_phone', 'password');

        $field = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $credentials['email_or_phone'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            // Создайте токен и добавьте к нему пользовательские данные
            $token = $user->createToken('api-token', ['email', 'name'])->plainTextToken;
            $role = $user->roles()->first()->id;
            $cookie = cookie('jwt', $token);
            return response([
                'message' => $token,
                'user_role' => $role
            ])->withCookie($cookie);
        } else {

            return response()->json(['message' => 'Unauthorize'], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL()
        ]);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return response()->json(['message' => 'You are Logouted ']);
    }


 
}
