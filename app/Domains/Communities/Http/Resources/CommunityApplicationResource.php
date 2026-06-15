<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Resources;

use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Communities\Models\CommunityApplication;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommunityApplication
 */
class CommunityApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposed_name' => $this->proposed_name,
            'description' => $this->description,
            'status' => $this->status?->value,
            'reason' => $this->reason,
            'community_id' => $this->community_id,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'user' => UserBriefResource::make($this->whenLoaded('user')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
