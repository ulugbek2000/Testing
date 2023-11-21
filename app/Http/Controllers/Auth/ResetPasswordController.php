<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    public function sendResetResponse(Request $request, $response)
    {
        return response(['status' => trans($response)]);
    }

    public function sendResetFailedResponse(Request $request, $response)
    {
        return response(['phone' => trans($response)], 422);
    }

    protected function sendResetLinkResponse($response)
    {
        return response(['status' => trans($response)]);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response(['phone' => trans($response)], 422);
    }

    protected function resetPassword($user, $password)
    {
        $user->password = bcrypt($password);
        $user->save();
    }
}
