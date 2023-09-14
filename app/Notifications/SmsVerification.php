<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use OsonSMS\SMSGateway\SMSGateway;

class SmsVerification extends Notification
{
    use Queueable;

    private $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'sms'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            $this->message
        ];
    }

    public function toSms(object $notifiable) {
        $txn_id = uniqid();
        $result = SMSGateway::Send($this->message['number'], $this->message['text'], $txn_id);

        if ($result) 
            return "SMS has been sent successfully";
        return "An error occurred while sending the SMS";
    }

}
