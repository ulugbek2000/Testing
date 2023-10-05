<?php

namespace App\Listeners;

use App\Enums\UserType;
use App\Notifications\VerificationNotification;
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
       dd($event);
        // Send sms verification notification
        $verificationNumber = rand(1000, 9999);
        $event->user->notify(new VerificationNotification($verificationNumber));
    }
}
