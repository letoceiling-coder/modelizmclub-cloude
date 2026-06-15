<?php

declare(strict_types=1);

namespace App\Domains\Users\Services;

use App\Domains\Users\Enums\VerificationCodeType;
use App\Domains\Users\Models\User;
use App\Domains\Users\Models\VerificationCode;
use App\Domains\Users\Notifications\VerificationCodeNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class VerificationCodeService
{
    public const MAX_ATTEMPTS = 5;

    public function generate(string $target, VerificationCodeType $type, ?User $user = null): VerificationCode
    {
        // Гасим предыдущие активные коды того же назначения
        VerificationCode::query()
            ->where('target', $target)
            ->where('type', $type->value)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        return VerificationCode::create([
            'user_id' => $user?->id,
            'target' => $target,
            'code' => (string) random_int(100000, 999999),
            'type' => $type->value,
            'expires_at' => now()->addMinutes($type->ttlMinutes()),
        ]);
    }

    public function send(VerificationCode $code): void
    {
        // В деве MAIL_MAILER=log — код попадёт в лог
        Notification::route('mail', $code->target)
            ->notify(new VerificationCodeNotification($code->code, $code->type));
    }

    public function generateAndSend(string $target, VerificationCodeType $type, ?User $user = null): VerificationCode
    {
        $code = $this->generate($target, $type, $user);
        $this->send($code);

        return $code;
    }

    /**
     * @throws ValidationException
     */
    public function verify(string $target, VerificationCodeType $type, string $code): VerificationCode
    {
        $record = VerificationCode::query()
            ->where('target', $target)
            ->where('type', $type->value)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages([
                'code' => 'Код подтверждения не найден или истёк. Запросите новый код.',
            ]);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => 'Превышено число попыток. Запросите новый код.',
            ]);
        }

        if (! hash_equals($record->code, $code)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'Неверный код подтверждения.',
            ]);
        }

        $record->update(['consumed_at' => now()]);

        return $record;
    }
}
