<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('app-token')->plainTextToken;
    
            return response()->json(['token' => $token], 200);
        }
    
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    
    
    }

    public function index(Request $request)
    {
        // Получите данные пользователя, который успешно аутентифицирован
        $user = $request->user();

        // Верните данные пользователя в виде JSON-ответа
        return response()->json(['user' => $user]);
    }

    public function checkToken(Request $request)
    {
        // Получите токен из запроса (например, из заголовка или параметра запроса)
        $token = $request->header('token');

        // Найдите пользователя по токену в базе данных
        $user = User::where('api_token', $token)->first();

        // Если пользователя с таким токеном не найдено, верните ошибку
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Если пользователь найден, верните данные
        return response()->json(['user' => $user]);
    }
}
