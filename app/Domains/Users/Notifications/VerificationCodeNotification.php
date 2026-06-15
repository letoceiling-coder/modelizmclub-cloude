<?php

declare(strict_types=1);

namespace App\Domains\Users\Notifications;

use App\Domains\Users\Enums\VerificationCodeType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public VerificationCodeType $type,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            VerificationCodeType::EmailVerification => 'Подтверждение почты — Моделизм',
            VerificationCodeType::PasswordReset => 'Сброс пароля — Моделизм',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Здравствуйте!')
            ->line('Ваш код подтверждения:')
            ->line('**'.$this->code.'**')
            ->line('Код действует ограниченное время. Никому его не сообщайте.')
            ->salutation('С уважением, команда «Моделизм»');
    }
}
