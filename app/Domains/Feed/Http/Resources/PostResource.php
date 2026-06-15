<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Resources;

use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Feed\Models\Post;
use App\Domains\Users\Http\Resources\UserBriefResource;
use App\Support\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status?->value,
            'is_pinned' => $this->is_pinned,
            'author' => UserBriefResource::make($this->whenLoaded('user')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'community' => $this->whenLoaded('community', fn () => [
                'id' => $this->community?->id,
                'name' => $this->community?->name,
                'slug' => $this->community?->slug,
            ]),
            'repost_of' => self::make($this->whenLoaded('repostOf')),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'photos' => MediaResource::collection($this->whenLoaded('media', fn () => $this->getMedia('photos'))),
            'video' => $this->whenLoaded('media', function () {
                $video = $this->getFirstMedia('video');

                return $video ? MediaResource::make($video) : null;
            }),
            'counts' => [
                'likes' => $this->likes_count,
                'comments' => $this->comments_count,
                'reposts' => $this->reposts_count,
                'views' => $this->views_count,
            ],
            'is_liked' => $this->when(
                $this->relationLoaded('reactions'),
                fn () => $this->reactions->isNotEmpty()
            ),
            'is_bookmarked' => $this->when(
                $this->relationLoaded('bookmarks'),
                fn () => $this->bookmarks->isNotEmpty()
            ),
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
