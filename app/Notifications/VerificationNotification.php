<?php

namespace App\Notifications;

use App\Broadcasting\SmsChannel;
use App\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use OsonSMS\SMSGateway\SMSGateway;

class VerificationNotification extends Notification
{
    use Queueable;

    private $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($verificationNumber)
    {
        $this->message = "Ваш проверочный номер {$verificationNumber}";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->phone != null ? ['database', SmsChannel::class] : ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->greeting('Здавствуйте')
                    ->line($this->message)
                    ->salutation('С наилучшими пожеланиями');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [$this->message];
    }

    public function toSms($notifiable)
    {
        return (new SmsMessage)
                    ->from(config('app.name'))
                    ->to($notifiable->phone)
                    ->line($this->message);
    }

}
