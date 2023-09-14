<?php

namespace App\Notifications;

use App\SmsMessage;
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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
         (new SmsMessage)
                    ->from(config('app.name'))
                    ->to($this->message['phone'])
                    ->line($this->message['text']);

        return [
            $this->message
        ];


    }

    public function toSms($notifiable)
    {
        return (new SmsMessage)
                    ->from(config('app.name'))
                    ->to($this->message['phone'])
                    ->line($this->message['text']);
    }

}
