<?php

declare(strict_types=1);

namespace App\Domains\Feed\Policies;

use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Post;
use App\Domains\Users\Models\User;

class PostPolicy
{
    public function view(?User $user, Post $post): bool
    {
        if ($post->status === PostStatus::Published) {
            return true;
        }

        return $user !== null && ($user->id === $post->user_id || $user->isModerator());
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isModerator();
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isModerator();
    }

    public function pin(User $user, Post $post): bool
    {
        if ($user->isModerator()) {
            return true;
        }

        // Владелец/модератор сообщества может закреплять посты в своём сообществе
        if ($post->community_id !== null) {
            return $post->community?->owner_id === $user->id
                || $post->community?->members()
                    ->where('user_id', $user->id)
                    ->where('role', 'moderator')
                    ->exists();
        }

        return false;
    }
}
