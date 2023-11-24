<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Notification;

class PasswordResetTokenController extends Controller
{

    public function sendCodeReset(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'Номер телефона не авторизован'], 404);
        }

        $verificationCode = rand(1000, 9999);

        $user->notify(new VerificationNotification($verificationCode));

        return response()->json(['message' => 'Код подтверждения отправлен'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'verification' => 'required|numeric',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/', 'confirmed'],
        ]);
    
        // Find the user by the verification code
        $user = User::whereHas('unreadNotifications', function ($query) use ($request) {
            $query->where('type', 'App\Notifications\VerificationNotification')
                ->whereJsonContains('data->verification', (int)$request->verification)
                ->whereNull('read_at');
        })->first();
        dd($user);
    
        if (!$user || !$user->verifyCode($request->verification)) {
            return response()->json(['error' => 'Неверный код подтверждения'], 422);
        }
    
        // Update the user's password
        $user->update(['password' => bcrypt($request->password)]);
    
        return response()->json(['message' => 'Пароль успешно изменен'], 200);
    }
    
}
