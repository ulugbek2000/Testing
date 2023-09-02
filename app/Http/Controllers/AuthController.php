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
}
