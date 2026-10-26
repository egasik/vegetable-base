<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCode extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Код подтверждения — Овощная база')->greeting('Здравствуйте!')
            ->line('Ваш код подтверждения: '.$this->code)->line('Код действует 10 минут. Никому его не сообщайте.')
            ->line('Если вы не запрашивали код, проигнорируйте письмо.');
    }
}
