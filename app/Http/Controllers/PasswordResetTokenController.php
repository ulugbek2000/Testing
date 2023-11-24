<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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

        if (!$user) {
            return response()->json(['message' => 'Номер телефона не авторизован'], 404);
        }

        $verificationCode = rand(1000, 9999);

        $user->notify(new VerificationNotification($verificationCode));

        return response()->json(['message' => 'Verification code sent'], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'verification' => 'required|numeric',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/', 'confirmed'],
        ]);
    
        // Найти уведомление по коду подтверждения
        $notification = Notification::where('data', $request->verification)->first();
    
        // Проверить, что уведомление существует и содержит notifiable типа User
        if ($notification && $notification->notifiable_type === User::class) {
            // Получить пользователя из уведомления
            $user = $notification->notifiable;
    
            // Обновить пароль пользователя
            $user->update(['password' => bcrypt($request->password)]);
    
            // Опционально: удалить или обновить уведомление после успешного сброса пароля
    
            return response()->json(['message' => 'Пароль успешно изменен'], 200);
        }
    
        return response()->json(['error' => 'Неверный код подтверждения'], 422);
    }
    
}
