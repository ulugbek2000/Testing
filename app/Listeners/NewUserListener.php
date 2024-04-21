<?php

namespace App\Listeners;

use App\Enums\UserType;
use App\Notifications\VerificationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewUserListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        // Assign newly registered user as student
        $event->user->assignRole(UserType::Student);
        // TODO create user wallet upon register
        $event->user->wallet()->create(['wallet' => 0]);

        // Send sms verification notification
        $verificationNumber = rand(1000, 9999);
        $event->user->notify(new VerificationNotification($verificationNumber));

        $customClaims = [
            'user_type' => $event->user->user_type,
            'is_phone_verified' => false, // предположим, что пользователь еще не подтвердил номер телефона
            'is_email_verified' => false, // предположим, что пользователь еще не подтвердил номер телефона
        ];

        $token = JWTAuth::fromUser($event->user, $customClaims);

    }
}
