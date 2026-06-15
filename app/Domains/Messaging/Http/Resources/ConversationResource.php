<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Http\Resources;

use App\Domains\Messaging\Models\Conversation;
use App\Domains\Users\Http\Resources\UserBriefResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'title' => $this->title,
            'community_id' => $this->community_id,
            'last_message' => MessageResource::make($this->whenLoaded('lastMessage')),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'participants' => UserBriefResource::collection(
                $this->whenLoaded('participants', fn () => $this->participants->map->user->filter())
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
