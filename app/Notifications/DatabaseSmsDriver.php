<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class DatabaseSmsDriver extends Notification
{
    use Queueable;

    public function send($notifiable, Notification $notification)
    {
        $smsData = $notification->toDatabaseSms($notifiable);

        // Сохраните SMS в базе данных
        DB::table('sms_messages')->insert([
            'phone_number' => $smsData['number'],
            'message' => $smsData['text'],
        ]);

     
    }
}
