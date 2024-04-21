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
        return [];
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
        // $message = $notification->toSms($notifiable);
        // // $message = $notification->toMail($notifiable);
        // $message->send();


        if ($notification instanceof \App\Notifications\VerificationNotification) {
            // Отправка SMS
            $message = $notification->toSms($notifiable);
            // Предполагается, что SmsMessage реализован соответствующим образом
            // и возвращает экземпляр \OsonSMS\SMSGateway\SMSGateway для отправки SMS
            $smsGateway = $message->send();
    
            // Отправка электронной почты
            $message = $notification->toMail($notifiable);
            $message->send();
    
            // Можете также записать логику отправки SMS-уведомления на ваш смс-шлюз здесь
            // $smsGateway->sendSMS($message->phone, $message->content);
        } else {
            // Обработка других типов уведомлений (например, уведомлений по электронной почте)
            $message = $notification->toMail($notifiable);
            $message->send();
        }
    }
}
