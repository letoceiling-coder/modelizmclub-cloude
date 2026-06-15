<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Users\Enums\VerificationCodeType;
use App\Domains\Users\Http\Requests\ForgotPasswordRequest;
use App\Domains\Users\Http\Requests\ResetPasswordRequest;
use App\Domains\Users\Models\User;
use App\Domains\Users\Services\VerificationCodeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly VerificationCodeService $codes,
    ) {}

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();
        $user = User::where('email', $email)->first();

        if ($user !== null) {
            $this->codes->generateAndSend($email, VerificationCodeType::PasswordReset, $user);
        }

        return response()->json(['message' => 'Если почта зарегистрирована, на неё отправлен код для сброса пароля.']);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->string('email')->toString();

        $this->codes->verify($email, VerificationCodeType::PasswordReset, $request->string('code')->toString());

        $user = User::where('email', $email)->firstOrFail();
        $user->forceFill(['password' => Hash::make($request->string('password')->toString())])->save();

        // Отзываем все токены — безопасность
        $user->tokens()->delete();

        return response()->json(['message' => 'Пароль успешно изменён.']);
    }
}
