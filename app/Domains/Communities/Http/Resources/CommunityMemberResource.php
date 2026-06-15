<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Resources;

use App\Domains\Communities\Models\CommunityMember;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommunityMember
 */
class CommunityMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role?->value,
            'joined_at' => $this->joined_at?->toIso8601String(),
            'user' => UserBriefResource::make($this->whenLoaded('user')),
        ];
    }
}
