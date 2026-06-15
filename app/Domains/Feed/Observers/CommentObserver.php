<?php

declare(strict_types=1);

namespace App\Domains\Feed\Observers;

use App\Domains\Feed\Models\Comment;

class CommentObserver
{
    public function creating(Comment $comment): void
    {
        if ($comment->parent_id !== null) {
            $parent = Comment::find($comment->parent_id);

            if ($parent !== null) {
                $comment->depth = $parent->depth + 1;
                $comment->root_id = $parent->root_id ?? $parent->id;
            }
        } else {
            $comment->depth = 0;
        }
    }

    public function created(Comment $comment): void
    {
        // Материализованный путь строим после получения id
        $parentPath = null;

        if ($comment->parent_id !== null) {
            $parentPath = Comment::whereKey($comment->parent_id)->value('path');
        }

        $comment->path = $parentPath
            ? $parentPath.'/'.$comment->id
            : (string) $comment->id;

        $comment->saveQuietly();

        $comment->post()->increment('comments_count');

        if ($comment->parent_id !== null) {
            Comment::whereKey($comment->parent_id)->increment('replies_count');
        }
    }

    public function deleted(Comment $comment): void
    {
        $comment->post()->decrement('comments_count');

        if ($comment->parent_id !== null) {
            Comment::whereKey($comment->parent_id)->decrement('replies_count');
        }
    }
}
