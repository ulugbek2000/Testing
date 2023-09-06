<?php

namespace App\Http\Controllers;

use App\Models\User;
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
                'photo' => $request->input('photo'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
            ]);
        } elseif ($request->has('phone')) {
            // Регистрация с использованием номера телефона
            $user = User::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
                'city' => $request->input('city'),
                'photo' => $request->input('photo'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
            ]);
        }

        return response()->json(['message' => 'Registration successful'], 201);






        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string',
        //     'surname' => 'required|string',
        //     'email' => 'required_without:phone|email|unique:users',
        //     'phone' => 'required_without:email|string|unique:users',
        //     'password' => 'required|string|min:8',
        //     'city' => 'nullable|string',
        //     'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,mov',
        //     'gender' => 'nullable|string|in:male,female,other',
        //     'date_of_birth' => 'nullable|date',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 400);
        // }

        // $user = new User([
        //     'name' => $request->name,
        //     'surname' => $request->surname,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        //     'city' => $request->city,
        //     'gender' => $request->gender,
        //     'date_of_birth' => $request->date_of_birth,
        // ]);
        // if ($request->has('photo')) {
        //     $user->photo = $request->photo;
        // }
        // $user->save();

        // return response()->json(['message' => 'Register succefully'], 201);
    }

    public function login(Request $request)
    {
        
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        $token = $user->createToken('api-token')->plainTextToken;

        $cookie = cookie('jwt', $token);
        return response([
            'message' => $token
        ])->withCookie($cookie);
    }
}
