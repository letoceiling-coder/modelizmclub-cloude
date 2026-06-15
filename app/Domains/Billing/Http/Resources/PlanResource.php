<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Resources;

use App\Domains\Billing\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
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
            'price' => $this->price,
            'period' => $this->period?->value,
            'photo_limit' => $this->photo_limit,
            'ad_priority' => $this->ad_priority,
            'free_ads_count' => $this->free_ads_count,
            'discount_percent' => $this->discount_percent,
            'badge' => $this->badge,
            'features' => $this->whenLoaded('features', fn () => $this->features->map(fn ($f) => [
                'key' => $f->key,
                'label' => $f->label,
                'enabled' => (bool) $f->pivot->enabled,
                'value' => $f->pivot->value,
            ])),
        ];
    }
}
