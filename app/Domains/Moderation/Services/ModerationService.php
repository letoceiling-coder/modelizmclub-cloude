<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Services;

use App\Domains\Communities\Enums\CommunityStatus;
use App\Domains\Communities\Models\Community;
use App\Domains\Feed\Enums\CommentStatus;
use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    public function decide(ModerationItem $item, User $reviewer, ModerationStatus $decision, ?string $reason = null): ModerationItem
    {
        return DB::transaction(function () use ($item, $reviewer, $decision, $reason): ModerationItem {
            $item->forceFill([
                'status' => $decision->value,
                'decision' => $decision->value,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'reason' => $reason,
            ])->save();

            $this->applyToContent($item->moderatable, $decision);

            return $item;
        });
    }

    private function applyToContent(mixed $content, ModerationStatus $decision): void
    {
        match (true) {
            $content instanceof Post => $this->applyToPost($content, $decision),
            $content instanceof Comment => $this->applyToComment($content, $decision),
            $content instanceof Community => $this->applyToCommunity($content, $decision),
            default => null,
        };
    }

    private function applyToPost(Post $post, ModerationStatus $decision): void
    {
        match ($decision) {
            ModerationStatus::Approved => $post->forceFill([
                'status' => PostStatus::Published->value,
                'published_at' => $post->published_at ?? now(),
                'moderated_at' => now(),
            ])->save(),
            ModerationStatus::Rejected => $post->forceFill([
                'status' => PostStatus::Rejected->value,
                'moderated_at' => now(),
            ])->save(),
            default => $post->forceFill(['status' => PostStatus::Pending->value])->save(),
        };
    }

    private function applyToComment(Comment $comment, ModerationStatus $decision): void
    {
        $comment->forceFill([
            'status' => $decision === ModerationStatus::Approved
                ? CommentStatus::Published->value
                : CommentStatus::Hidden->value,
        ])->save();
    }

    private function applyToCommunity(Community $community, ModerationStatus $decision): void
    {
        $community->forceFill([
            'status' => $decision === ModerationStatus::Approved
                ? CommunityStatus::Active->value
                : CommunityStatus::Blocked->value,
        ])->save();
    }
}
