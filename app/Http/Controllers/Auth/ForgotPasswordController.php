<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLink(Request $request)
    {
        $this->validate($request, ['phone' => 'required|exists:users,phone']);

        $response = Password::sendResetLink(
            $request->only('phone'),
            new ResetPasswordNotification(url('/password/reset'))
        );

        return $response == Password::RESET_LINK_SENT
            ? ['status' => trans($response)]
            : response(['phone' => trans($response)], 422);
    }
}
