<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSkills;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

// use Illuminate\Validation\ValidationException;
class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $credentials = $request->only('email_or_phone', 'password');

        $field = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $credentials['email_or_phone'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            $role = $user->roles()->first()->id;
            // Создайте пользовательские данные для токена
            $customClaims = [
                'user_type' => $role,
                'is_phone_verified' => $user->phone_verified_at != null,
                'email' => $user->email,
                'name' => $user->name,
            ];

            // Создайте JWT токен с пользовательскими данными
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            return response([
                'token' => $token,
            ]);
        } else {
            return response()->json(['message' => 'Неверные пароль или телефон'], 401);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL()
        ]);
    }

    public function logout()
    {
        Auth::logout();
        if ($user = auth()->user()) {
            // User is logged in and you can access the tokens
            $user->tokens->each(function ($token, $key) {
                $token->revoke();
            });
        }
    }


    public function verifyPhoneNumber(Request $request)
    {
        $user = Auth::user();
        $role = $user->roles()->first()->id;
        $verificationCode = $request->input('verification');

        // Проверяем верификацию и устанавливаем phone_verified_at, если успешно
        if ($user->verifyCode($verificationCode)) {
            $user->phone_verified_at = now(); // Устанавливаем phone_verified_at
            $user->save(); // Сохраняем изменения в базе данных
        }

        // Обновляем значение is_phone_verified в $customClaims
        $customClaims = [
            'user_type' => $role,
            'is_phone_verified' => $user->phone_verified_at != null,
        ];

        // Создаем новый JWT токен с обновленными пользовательскими данными
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return $user->phone_verified_at != null
            ? response()->json(['message' => 'Verification Completed', 'token' => $token], 200)
            : response()->json(['message' => 'Verification Failed'], 406);
    }

    // public function forgotPassword(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         throw ValidationException::withMessages([
    //             'phone' => [trans('validation.required', ['attribute' => 'phone'])],
    //         ]);
    //     }


    //     $user = User::where('phone', $request->phone)->first();
    //     if ($user) {
    //         $code =
    //             $user->notify(new VerificationNotification($code));
    //     }

    //     return response(['status' => 'SMS sent successfully']);
        
    // }



    public function sendResetLink(Request $request)
    {
        $request->validate(['phone_number' => 'required|phone_number']);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        Password::sendResetLink($user);

        return response()->json(['message' => 'Password reset link sent'], 200);
    }
}
