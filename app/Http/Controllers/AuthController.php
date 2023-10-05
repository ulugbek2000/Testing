<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserSkills;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            // dd($role);
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
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        // $credentials = $request->only('email_or_phone', 'password');

        // $field = filter_var($credentials['email_or_phone'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // if (Auth::attempt([$field => $credentials['email_or_phone'], 'password' => $credentials['password']])) {
        //     $user = Auth::user();

        //     // Создайте токен и добавьте к нему пользовательские данные
        //     $token = $user->createToken('api-token', ['email', 'name'])->plainTextToken;
        //     $role = $user->roles()->first()->id;
        //     $cookie = cookie('jwt', $token);
        //     return response([
        //         'message' => $token,
        //         'user_type' => $role,
        //         'is_phone_verified' => $user->phone_verified_at != null,
        //     ])->withCookie($cookie);
        // } else {

        //     return response()->json(['message' => 'Unauthorize'], 401);
        // }
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
    function verifyPhoneNumber(Request $request)
    {
        $user = Auth::user();
        return $user->verifyCode($request->input('verification')) === true
            ? response()->json(['message' => 'Verification Completed'], 200)
            : response()->json(['message' => 'Verification Failed'], 406);
    }
}
