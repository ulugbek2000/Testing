<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetTokenController extends Controller
{


    public function sendResetLink(Request $request)
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
                'verification_code' => $verificationCode,
            ]);
    
            return response()->json(['message' => 'Verification code sent'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
}
