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

        $user = Auth::user();

        $verificationCode = rand(1000, 9999);

        if ($user->phone == $request->phone) {
            $user->notify(new VerificationNotification($verificationCode));
        }

            PasswordResetToken::create([
                'email' => $user->email,
                'phone' => $user->phone,
                'token' => Hash::make($verificationCode),
            ]);

            return response()->json(['message' => 'Verification code sent'], 200);
        
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|numeric',
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/', 'confirmed'],
        ]);

        $user = Auth::user();

        $verificationCode = $request->input('verification_code');

        if (!$user->verifyCode($verificationCode)) {
            return response()->json(['error' => 'Неверный код подтверждения'], 422);
        }

        
        $user->update(['password' => bcrypt($request->password)]);

        return response()->json(['message' => 'Пароль успешно изменен'], 200);
    }
}
