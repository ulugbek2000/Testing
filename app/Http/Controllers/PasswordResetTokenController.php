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

        $notification = DB::table('notifications')
        ->where('data', $request->verification)
        ->first();
        dd($user = User::find($notification->notifiable_id));
        if ($notification && $notification->notifiable_type === 'App\Models\User') {
            $user = User::find($notification->notifiable_id);
    
                $user->update(['password' => bcrypt($request->password)]);
                return response()->json(['message' => 'Пароль успешно изменен'], 200);
        }
    
        return response()->json(['error' => 'Неверный код подтверждения'], 422);
    }
    
}
