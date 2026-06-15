<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Resources;

use App\Domains\Moderation\Models\Report;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin Report
 */
class ReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'description' => $this->description,
            'status' => $this->status?->value,
            'resolution' => $this->resolution,
            'target_type' => Str::afterLast((string) $this->reportable_type, '\\'),
            'target_id' => $this->reportable_id,
            'reporter' => UserBriefResource::make($this->whenLoaded('reporter')),
            'handler' => UserBriefResource::make($this->whenLoaded('handler')),
            'created_at' => $this->created_at?->toIso8601String(),
            'handled_at' => $this->handled_at?->toIso8601String(),
        ];
    }
}
