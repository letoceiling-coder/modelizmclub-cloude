<?php

declare(strict_types=1);

namespace App\Domains\Ads\Http\Resources;

use App\Domains\Ads\Models\Ad;
use App\Domains\Catalog\Http\Resources\CategoryResource;
use App\Domains\Catalog\Http\Resources\CityResource;
use App\Domains\Users\Http\Resources\UserBriefResource;
use App\Support\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ad
 */
class AdResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'condition' => $this->condition?->value,
            'status' => $this->status?->value,
            'contact_via_chat' => $this->contact_via_chat,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'author' => UserBriefResource::make($this->whenLoaded('user')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'city' => CityResource::make($this->whenLoaded('city')),
            'photos' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
