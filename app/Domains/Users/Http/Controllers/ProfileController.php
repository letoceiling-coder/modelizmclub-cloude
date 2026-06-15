<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Controllers;

use App\Domains\Users\Http\Requests\SyncInterestsRequest;
use App\Domains\Users\Http\Requests\UpdatePrivacyRequest;
use App\Domains\Users\Http\Requests\UpdateProfileRequest;
use App\Domains\Users\Http\Resources\UserResource;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Support\Media\ImageProcessor;
use App\Support\Media\UploadPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /** Публичный профиль пользователя. */
    public function show(User $user): UserResource
    {
        return UserResource::make($user->load(['city', 'interests', 'roles']));
    }

    /** Обновление своего профиля. */
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->fill($request->validated())->save();

        return UserResource::make($user->fresh(['city', 'interests', 'roles']));
    }

    /** Настройки приватности. */
    public function updatePrivacy(UpdatePrivacyRequest $request): UserResource
    {
        $user = $request->user();
        $user->forceFill(['privacy' => $request->validated()['privacy']])->save();

        return UserResource::make($user->fresh());
    }

    /** Выбор интересов (категорий). */
    public function syncInterests(SyncInterestsRequest $request): UserResource
    {
        $user = $request->user();
        $user->interests()->sync($request->validated()['categories']);

        return UserResource::make($user->fresh('interests'));
    }

    /** Загрузка аватара: приводится к WebP и квадратной обрезке. */
    public function uploadAvatar(Request $request): UserResource
    {
        $request->validate([
            'avatar' => array_merge(['required'], UploadPolicy::fileRules('avatar')),
        ]);

        $user = $request->user();
        $disk = config('uploads.disk', 'public');

        $conversion = (array) config('uploads.profiles.avatar.conversions.medium', ['width' => 512, 'height' => 512]);
        $path = ImageProcessor::storeWebp(
            $request->file('avatar'),
            'avatars',
            (int) ($conversion['width'] ?? 512),
            (int) ($conversion['height'] ?? 512),
            $disk,
        );

        // Удаляем прежний аватар, чтобы не копить мусор.
        $oldPath = $user->getAttributes()['avatar_path'] ?? null;
        if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
            Storage::disk($disk)->delete($oldPath);
        }

        $user->forceFill(['avatar_path' => $path])->save();

        return UserResource::make($user->fresh());
    }

    /** Заблокировать пользователя (чёрный список). */
    public function block(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Нельзя заблокировать самого себя.');

        $request->user()->blockedUsers()->syncWithoutDetaching([$user->id]);

        return response()->json(['message' => 'Пользователь добавлен в чёрный список.']);
    }

    /** Разблокировать пользователя. */
    public function unblock(Request $request, User $user): JsonResponse
    {
        $request->user()->blockedUsers()->detach($user->id);

        return response()->json(['message' => 'Пользователь убран из чёрного списка.']);
    }
}
