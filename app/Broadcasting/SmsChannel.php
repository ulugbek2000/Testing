<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        //
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Remember that we created the toSms() methods in our notification class
        // Now is the time to use it.
        // In our example. $notifiable is an instance of a User that just signed up.
        $message = $notification->toSms($notifiable);

        // Now we hopefully have a instance of a SmsMessage.
        // That we are ready to send to our user.
        // Let's do it :-)
        $message->send();

       // Or use dryRun() for testing to send it, without sending it for real.
        $message->send();

        // Wait.. was that it?
        // Well sort of.. :-)
        // But we need to implement this magical SmsMessage class for it to work.

    }
}
