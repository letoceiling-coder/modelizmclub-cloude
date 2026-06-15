<?php

declare(strict_types=1);

namespace App\Domains\Feed\Policies;

use App\Domains\Feed\Models\Comment;
use App\Domains\Users\Models\User;

class CommentPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->user_id
            || $user->isModerator()
            || $comment->post?->user_id === $user->id;
    }
}
