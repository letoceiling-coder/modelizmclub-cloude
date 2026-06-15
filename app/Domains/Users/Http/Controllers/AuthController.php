<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Users\Http\Requests\LoginRequest;
use App\Domains\Users\Http\Requests\RegisterRequest;
use App\Domains\Users\Http\Resources\UserResource;
use App\Domains\Users\Services\AuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    /**
     * Регистрация по почте. Отправляется код подтверждения, выдаётся токен.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated(), $request);
        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;

        return UserResource::make($user->load('roles'))
            ->additional([
                'token' => $token,
                'message' => 'Регистрация завершена. На почту отправлен код подтверждения.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Вход по почте и паролю. Возвращает токен Sanctum.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->input('device_name', 'api'),
        );

        return UserResource::make($result['user']->load('roles'))
            ->additional(['token' => $result['token']])
            ->response();
    }

    /**
     * Текущий пользователь.
     */
    public function me(Request $request): UserResource
    {
        return UserResource::make(
            $request->user()->load(['roles', 'city', 'interests'])
        );
    }

    /**
     * Выход: отзыв текущего токена.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Вы вышли из системы.']);
    }
}
