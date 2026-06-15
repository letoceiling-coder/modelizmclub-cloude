<?php

declare(strict_types=1);

use App\Domains\Messaging\Models\Conversation;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Личные уведомления пользователя
Broadcast::channel('App.Models.User.{id}', fn (User $user, int $id) => (int) $user->id === $id);
Broadcast::channel('users.{id}', fn (User $user, int $id) => (int) $user->id === $id);

// Приватный канал диалога — только активные участники
Broadcast::channel('conversations.{conversation}', function (User $user, int $conversation): bool {
    return Conversation::query()
        ->whereKey($conversation)
        ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id)->whereNull('left_at'))
        ->exists();
});
