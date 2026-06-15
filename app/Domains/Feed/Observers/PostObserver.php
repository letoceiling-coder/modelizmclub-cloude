<?php

declare(strict_types=1);

namespace App\Domains\Feed\Observers;

use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Post;

class PostObserver
{
    public function creating(Post $post): void
    {
        if ($post->status === PostStatus::Published && $post->published_at === null) {
            $post->published_at = now();
        }
    }

    public function created(Post $post): void
    {
        if ($post->repost_of_id !== null) {
            Post::whereKey($post->repost_of_id)->increment('reposts_count');
        }

        if ($post->community_id !== null) {
            $post->community()->increment('posts_count');
        }
    }

    public function updating(Post $post): void
    {
        if ($post->isDirty('status')
            && $post->status === PostStatus::Published
            && $post->published_at === null) {
            $post->published_at = now();
        }
    }

    public function deleted(Post $post): void
    {
        if ($post->repost_of_id !== null) {
            Post::whereKey($post->repost_of_id)->decrement('reposts_count');
        }

        if ($post->community_id !== null) {
            $post->community()->decrement('posts_count');
        }
    }
}
