<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\Role;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Http\Resources\UserResource;
use App\Domains\Users\Models\SocialAccount;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role as SpatieRole;
use Symfony\Component\HttpFoundation\Response;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['vkontakte', 'yandex'];

    public function redirect(string $provider): JsonResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), Response::HTTP_NOT_FOUND);

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function callback(string $provider): JsonResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), Response::HTTP_NOT_FOUND);

        $socialUser = Socialite::driver($provider)->stateless()->user();

        $user = DB::transaction(function () use ($provider, $socialUser): User {
            $account = SocialAccount::where('provider', $provider)
                ->where('provider_user_id', (string) $socialUser->getId())
                ->first();

            if ($account !== null) {
                return $account->user;
            }

            $email = $socialUser->getEmail();
            $user = $email ? User::where('email', $email)->first() : null;

            if ($user === null) {
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Пользователь',
                    'email' => $email ?: $provider.'_'.$socialUser->getId().'@social.local',
                    'password' => bcrypt(Str::random(32)),
                    'email_verified_at' => $email ? now() : null,
                    'avatar_path' => null,
                    'status' => UserStatus::Active->value,
                ]);

                SpatieRole::findOrCreate(Role::User->value, 'web');
                $user->assignRole(Role::User->value);
                BonusAccount::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_user_id' => (string) $socialUser->getId(),
                'nickname' => $socialUser->getNickname(),
                'avatar' => $socialUser->getAvatar(),
                'access_token' => $socialUser->token ?? null,
            ]);

            return $user;
        });

        $token = $user->createToken($provider)->plainTextToken;

        return UserResource::make($user->load('roles'))
            ->additional(['token' => $token])
            ->response();
    }
}
