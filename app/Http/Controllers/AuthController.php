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
    
        $isPhoneLogin = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? false : true;
    
        $field = $isPhoneLogin ? 'phone' : 'email';
    
        if (Auth::attempt([$field => $credentials['email_or_phone'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            $role = $user->roles()->first()->id;
    
            $isPhoneVerified = $user->phone_verified_at != null;
            $isEmailVerified = $user->email_verified_at != null;
    
            // Создайте пользовательские данные для токена
            $customClaims = [
                'user_type' => $role,
                'is_phone_verified' => $isPhoneVerified,
                'is_email_verified' => $isEmailVerified,
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
    

    public function blockUser(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'заблокировано' : 'разблокировано';

        return response()->json(['message' => "Пользователь успешно $status"], 200);
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
    
        // Проверяем, была ли верифицирована по телефону или по электронной почте
        $verificationType = $user->phone ? 'phone' : 'email';
        dd($verificationType);
    
        // Проверяем верификацию и устанавливаем соответствующий атрибут
        if ($user->verifyCode($verificationCode, $verificationType)) {
            $user->{$verificationType.'_verified_at'} = now(); // Устанавливаем соответствующий атрибут
            $user->save(); // Сохраняем изменения в базе данных
        }
    
        // Обновляем значение is_phone_verified в $customClaims
        $customClaims = [
            'user_type' => $role,
            'is_phone_verified' => $user->phone_verified_at != null,
            'is_email_verified' => $user->email_verified_at != null,
        ];
    
        // Создаем новый JWT токен с обновленными пользовательскими данными
        $token = JWTAuth::claims($customClaims)->fromUser($user);
    
        return $user->{$verificationType.'_verified_at'} != null
            ? response()->json(['message' => 'Проверка завершена', 'token' => $token], 200)
            : response()->json(['message' => 'Проверка не удалась'], 406);
    }
    

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[0-9])(?=.*[a-zA-Z]).*$/', 'confirmed'],
        ], $this->validationMessages());

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'Старый пароль неверен'], 422);
        }

        // Обновляем пароль пользователя
        $user->update(['password' => bcrypt($request->password)]);

        return response()->json(['message' => 'Пароль успешно изменен'], 200);
    }

    private function validationMessages()
    {
        return [
            'password.confirmed' => 'Пароль и подтверждение пароля не совпадают.',
            'password.regex' => 'Пароль должен содержать как минимум одну букву и одну цифру.',
        ];
    }
}
