<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Users\Enums\VerificationCodeType;
use App\Domains\Users\Http\Requests\ResendCodeRequest;
use App\Domains\Users\Http\Requests\VerifyEmailRequest;
use App\Domains\Users\Models\User;
use App\Domains\Users\Services\AuthService;
use App\Domains\Users\Services\VerificationCodeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly VerificationCodeService $codes,
    ) {}

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $this->auth->verifyEmail(
            $request->string('email')->toString(),
            $request->string('code')->toString(),
        );

        return response()->json(['message' => 'Почта успешно подтверждена.']);
    }

    public function resend(ResendCodeRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();
        $user = User::where('email', $email)->first();

        // Не раскрываем существование пользователя
        if ($user !== null && $user->email_verified_at === null) {
            $this->codes->generateAndSend($email, VerificationCodeType::EmailVerification, $user);
        }

        return response()->json(['message' => 'Если почта зарегистрирована и не подтверждена, код отправлен повторно.']);
    }
}
