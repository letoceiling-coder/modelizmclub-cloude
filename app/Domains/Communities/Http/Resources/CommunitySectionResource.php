<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Resources;

use App\Domains\Communities\Models\CommunitySection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CommunitySection
 */
class CommunitySectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'community_id' => $this->community_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'position' => $this->position,
            'is_active' => $this->is_active,
        ];
    }
}
