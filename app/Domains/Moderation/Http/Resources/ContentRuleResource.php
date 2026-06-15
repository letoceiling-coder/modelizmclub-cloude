<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Resources;

use App\Domains\Moderation\Models\ContentRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContentRule
 */
class ContentRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'value' => $this->value,
            'action' => $this->action?->value,
            'is_active' => $this->is_active,
        ];
    }
}
