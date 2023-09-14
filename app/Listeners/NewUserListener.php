<?php

namespace App\Listeners;

use App\Enums\UserType;
use App\Notifications\SmsVerification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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

        // Send sms verification notification
        $verificationNumber = rand(1000, 9999);
        $event->user->notify(new SmsVerification(
            [
                'text' => "Ваш проверочный номер {$verificationNumber}", 
                'verification' => $verificationNumber
            ]
            ));
    }
}
