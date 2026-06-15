<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Http\Resources;

use App\Domains\Messaging\Models\Message;
use App\Domains\Users\Http\Resources\UserBriefResource;
use App\Support\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Message
 */
class MessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'type' => $this->type?->value,
            'body' => $this->body,
            'reply_to_id' => $this->reply_to_id,
            'edited_at' => $this->edited_at?->toIso8601String(),
            'author' => UserBriefResource::make($this->whenLoaded('user')),
            'attachments' => MediaResource::collection($this->whenLoaded('media')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
