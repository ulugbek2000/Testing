<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
     
        $request->validate( [
            'phone' => 'required|string',
        ]);

        $status = Password::sendResetLink(
            $request->only('phone'),
            function ($message) {
                $message->subject($this->getEmailSubject());
            }
        );

        return $status === Password::RESET_LINK_SENT
            ? response(['status' => __($status)])
            : response(['phone' => __($status)], 422);
    
    }
}
