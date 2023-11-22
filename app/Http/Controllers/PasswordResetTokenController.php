<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            // Create and save a new PasswordResetToken instance
            $token = new PasswordResetToken([
                'email' => $user->email,
                'phone' => $user->phone,
                'token' => Hash::make($verificationCode),
            ]);
    
            $token->save();
    
            // You can send the verification code through a notification, SMS, etc.
    
            return response()->json(['message' => 'Verification code sent'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
