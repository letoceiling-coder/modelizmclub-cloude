<?php

declare(strict_types=1);

use App\Domains\Feed\Models\Post;
use App\Domains\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    config()->set('uploads.disk', 'public');
    config()->set('media-library.disk_name', 'public');
    // Конверсии выполняем синхронно в тестах.
    config()->set('queue.default', 'sync');
    config()->set('media-library.queue_conversions_by_default', false);
});

it('uploads a post photo and generates webp conversions', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'Пост с фото',
        'photos' => [UploadedFile::fake()->image('tank.jpg', 1200, 900)],
    ])->assertCreated();

    $postId = $response->json('data.id');
    $post = Post::findOrFail($postId);

    $media = $post->getFirstMedia('photos');
    expect($media)->not->toBeNull();
    expect($media->hasGeneratedConversion('thumb'))->toBeTrue();
    expect($media->hasGeneratedConversion('medium'))->toBeTrue();
    // thumb должен быть webp
    expect($media->getPath('thumb'))->toEndWith('.webp');
});

it('rejects a photo with a disallowed extension', function () {
    $user = User::factory()->create();

    actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'Плохой файл',
        'photos' => [UploadedFile::fake()->create('malware.php', 10, 'application/x-php')],
    ])->assertStatus(422);
});

it('rejects an oversized photo', function () {
    $user = User::factory()->create();
    $maxKb = (int) config('uploads.profiles.post_photo.max_size_kb');

    actingAs($user)->postJson('/api/v1/posts', [
        'body' => 'Слишком большое фото',
        'photos' => [UploadedFile::fake()->create('huge.jpg', $maxKb + 1024, 'image/jpeg')],
    ])->assertStatus(422);
});

it('converts an uploaded avatar to webp', function () {
    $user = User::factory()->create();

    actingAs($user)->postJson('/api/v1/profile/avatar', [
        'avatar' => UploadedFile::fake()->image('me.png', 800, 800),
    ])->assertOk();

    $path = $user->fresh()->avatar_path;
    expect($path)->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
});
