<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Resources;

use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * @mixin ModerationItem
 */
class ModerationItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'decision' => $this->decision,
            'reason' => $this->reason,
            'flags' => $this->flags ?? [],
            'content_type' => Str::afterLast((string) $this->moderatable_type, '\\'),
            'content_id' => $this->moderatable_id,
            'content' => $this->whenLoaded('moderatable', fn () => $this->summarizeContent()),
            'submitter' => UserBriefResource::make($this->whenLoaded('submitter')),
            'reviewer' => UserBriefResource::make($this->whenLoaded('reviewer')),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeContent(): array
    {
        $content = $this->moderatable;

        return [
            'id' => $content?->id,
            'title' => $content->title ?? $content->name ?? null,
            'excerpt' => Str::limit((string) ($content->body ?? $content->description ?? ''), 200),
        ];
    }
}
