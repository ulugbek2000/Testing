<?php

namespace App\Notifications;

use App\Broadcasting\SmsChannel;
use App\SmsMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use OsonSMS\SMSGateway\SMSGateway;
use Symfony\Component\Mailer\MailerInterface;

class VerificationNotification extends Notification
{
    use Queueable;

    private $message, $no;

    /**
     * Create a new notification instance.
     */
    public function __construct($verificationNumber)
    {
        $this->message = "Ваш проверочный номер {$verificationNumber}";
        $this->no = $verificationNumber;
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





    public function toMail(object $notifiable = null)
    {
        $resetUrl = 'testEmail';
        $emailCredentials = [
            'title' => 'This is the reset password link',
            'resetUrl' => $resetUrl,
        ];

        Mail::send('emails.reset', $emailCredentials, function ($message) {
            $message->to('nusratzodasuhaib@gmail.com'); 
            $message->subject('Nikah RESET PASS');
        });
    }
  

    // public function toMail(object $notifiable)
    // {
    //     // return (new Mail){


    //     Mail::send('emails.reset', $emailCredentials, function ($message) {
    //         $message->to($this->message->email);
    //         $message->subject('Nikah RESET PASS');
    //     });
    //                 // ->to($notifiable->email)
    //                 // ->line($this->message)
    //                 // ->salutation('С наилучшими пожеланиями');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [$this->message, 'verification' => $this->no];
    }

    public function toSms($notifiable)
    {
        return (new SmsMessage)
            ->from(config('app.name'))
            ->to($notifiable->phone)
            ->line($this->message);
    }
}
