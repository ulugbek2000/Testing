<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetTokenController extends Controller
{


    public function sendCodeReset(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $verificationCode = rand(1000, 9999);

        try {
            $user->notify(new VerificationNotification($verificationCode));

            PasswordResetToken::create([
                'email' => $user->email,
                'phone' => $user->phone,
                'token' => Hash::make($verificationCode),
            ]);

            return response()->json(['message' => 'Verification code sent'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric',
            'password' => ['required','string','min:8','regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/','confirmed'],
        ]);

        $user = Auth::user();

        // Поиск пользователя по коду подтверждения
        $storedToken = PasswordResetToken::where('verification_code', $request->verification_code)->first();

        if (!$storedToken) {
            return response()->json(['error' => 'Код подтверждения не найден'], 404);
        }



        // Проверяем код подтверждения
        if (!Hash::check($request->verification_code, $storedToken->token)) {
            return response()->json(['error' => 'Неверный код подтверждения'], 422);
        }

        // Обновляем пароль пользователя
        $user->update(['password' => bcrypt($request->password)]);

        return response()->json(['message' => 'Пароль успешно изменен'], 200);
    }
}
