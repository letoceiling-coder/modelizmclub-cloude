<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Resources;

use App\Domains\Feed\Models\Comment;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'root_id' => $this->root_id,
            'depth' => $this->depth,
            'body' => $this->body,
            'status' => $this->status?->value,
            'author' => UserBriefResource::make($this->whenLoaded('user')),
            'counts' => [
                'likes' => $this->likes_count,
                'replies' => $this->replies_count,
            ],
            'replies' => self::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
