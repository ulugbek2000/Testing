<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class DatabaseSmsDriver extends Notification
{
    // use Queueable;
    // private $number;
    // private $text;

    // public function __construct($message)
    // {
    //     $this->number = $message['number'];
    //     $this->text = $message['text'];
    // } 
    
    // public function send($notifiable, Notification $notification)
    // {
    //     $smsData = $notification->toSms($notifiable);

        
    //     DB::table('sms_messages')->insert([
    //         'phone_number' => $smsData['number'],
    //         'message' => $smsData['text'],
    //     ]);
    // }

    // public function toDatabase($notifiable)
    // {
    //     return [
    //         'number' => $this->number, 
    //         'text' => $this->text,
    //     ];
    // }
}
