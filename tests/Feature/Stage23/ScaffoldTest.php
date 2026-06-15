<?php

declare(strict_types=1);

use App\Domains\Messaging\Models\Message;
use App\Domains\Users\Models\User;
use Database\Seeders\PlanSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

it('exposes the public price list', function () {
    seed(PlanSeeder::class);

    getJson('/api/v1/plans')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'price', 'period', 'features']]]);
});

it('returns an empty banner list by default', function () {
    getJson('/api/v1/banners')
        ->assertOk()
        ->assertJsonPath('data', []);
});

it('opens a private conversation and sends a message', function () {
    $author = User::factory()->create();
    $recipient = User::factory()->create();

    $conversationId = actingAs($author)
        ->postJson('/api/v1/conversations', ['recipient_id' => $recipient->id])
        ->assertSuccessful()
        ->json('data.id');

    actingAs($author)
        ->postJson("/api/v1/conversations/{$conversationId}/messages", ['body' => 'Привет!'])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Привет!');

    expect(Message::query()->where('conversation_id', $conversationId)->count())->toBe(1);
});

it('blocks non-participants from reading a conversation', function () {
    $author = User::factory()->create();
    $recipient = User::factory()->create();
    $stranger = User::factory()->create();

    $conversationId = actingAs($author)
        ->postJson('/api/v1/conversations', ['recipient_id' => $recipient->id])
        ->assertSuccessful()
        ->json('data.id');

    actingAs($stranger)
        ->getJson("/api/v1/conversations/{$conversationId}/messages")
        ->assertForbidden();
});

it('accepts a support ticket from an authenticated user', function () {
    actingAs(User::factory()->create())
        ->postJson('/api/v1/support/tickets', [
            'subject' => 'Не приходит код',
            'body' => 'При регистрации не приходит письмо с кодом.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'open');
});

it('queues an ad AI draft request', function () {
    actingAs(User::factory()->create())
        ->postJson('/api/v1/ads/ai-drafts', [
            'title' => 'Модель танка Т-34',
            'keywords' => ['1:35', 'звезда'],
        ])
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'pending');
});
