<?php

declare(strict_types=1);

use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Users\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('lists only published posts in the feed', function () {
    Post::factory()->count(3)->create();
    Post::factory()->draft()->create();

    $response = getJson('/api/v1/posts');

    $response->assertOk()->assertJsonCount(3, 'data');
});

it('hides a draft post from guests but shows it to its author', function () {
    $author = User::factory()->create();
    $draft = Post::factory()->draft()->for($author, 'user')->create();

    getJson("/api/v1/posts/{$draft->id}")->assertForbidden();

    actingAs($author)
        ->getJson("/api/v1/posts/{$draft->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $draft->id);
});

it('lets an authenticated user create a text post', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/v1/posts', [
            'body' => 'Собрал новый танк, делюсь результатом! #броня',
        ])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Собрал новый танк, делюсь результатом! #броня');

    expect(Post::query()->where('user_id', $user->id)->count())->toBe(1);
});

it('rejects post creation for guests', function () {
    postJson('/api/v1/posts', ['body' => 'aaa'])->assertUnauthorized();
});

it('forbids editing a post by a non-author', function () {
    $post = Post::factory()->create();
    $stranger = User::factory()->create();

    actingAs($stranger)
        ->patchJson("/api/v1/posts/{$post->id}", ['body' => 'hacked'])
        ->assertForbidden();
});

it('builds a comment tree with replies counter', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $root = actingAs($user)
        ->postJson("/api/v1/posts/{$post->id}/comments", ['body' => 'Корневой комментарий'])
        ->assertCreated()
        ->json('data.id');

    actingAs($user)
        ->postJson("/api/v1/posts/{$post->id}/comments", [
            'body' => 'Ответ',
            'parent_id' => $root,
        ])
        ->assertCreated()
        ->assertJsonPath('data.depth', 1);

    getJson("/api/v1/posts/{$post->id}/comments")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.counts.replies', 1);
});

it('toggles a like on a post and updates the counter', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    actingAs($user)
        ->postJson("/api/v1/posts/{$post->id}/reactions")
        ->assertOk()
        ->assertJsonPath('reacted', true)
        ->assertJsonPath('likes_count', 1);

    actingAs($user)
        ->postJson("/api/v1/posts/{$post->id}/reactions")
        ->assertOk()
        ->assertJsonPath('reacted', false)
        ->assertJsonPath('likes_count', 0);
});

it('toggles a bookmark and lists it', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    actingAs($user)
        ->postJson("/api/v1/posts/{$post->id}/bookmark")
        ->assertOk()
        ->assertJsonPath('bookmarked', true);

    actingAs($user)
        ->getJson('/api/v1/bookmarks')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
