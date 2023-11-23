<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;

class PasswordResetTokenController extends Controller
{


    public function sendCodeReset(Request $request)
    {
        $request->validate(['phone' => 'required|string']);
    
        $user = User::where('phone', $request->phone)->first();
    
    
        $verificationCode = rand(1000, 9999);
    
        if ($user->phone == $request->phone) {
            $user->notify(new VerificationNotification($verificationCode));
    
            return response()->json(['message' => 'Verification code sent'], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'verification' => 'required|numeric',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/', 'confirmed'],
        ]);
    
        $user = Auth::user();
        dd($user);
    
        // Поиск пользователя по коду подтверждения
        $verificationCode = $request->input('verification');
    
        // Проверяем верификацию и устанавливаем phone_verified_at, если успешно
        if ($user->verifyCode($verificationCode)) {
            $user->update(['password' => bcrypt($request->password)]);
            return response()->json(['message' => 'Пароль успешно изменен'], 200);
        }
    
        return response()->json(['error' => 'Неверный код подтверждения'], 422);
    }
}