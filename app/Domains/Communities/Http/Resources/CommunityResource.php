<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Resources;

use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Communities\Models\Community;
use App\Domains\Users\Http\Resources\UserBriefResource;
use App\Support\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Community
 */
class CommunityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status?->value,
            'avatar' => MediaResource::make($this->getFirstMedia('avatar')),
            'cover' => MediaResource::make($this->getFirstMedia('cover')),
            'counts' => [
                'members' => $this->members_count,
                'posts' => $this->posts_count,
            ],
            'owner' => UserBriefResource::make($this->whenLoaded('owner')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'sections' => CommunitySectionResource::collection($this->whenLoaded('sections')),
            'viewer_membership' => $this->whenLoaded('members', function () {
                $member = $this->members->first();

                return $member === null ? null : [
                    'role' => $member->role?->value,
                    'joined_at' => $member->joined_at?->toIso8601String(),
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
