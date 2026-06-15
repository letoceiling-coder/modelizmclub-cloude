<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Services;

use App\Domains\Messaging\Enums\ConversationType;
use App\Domains\Messaging\Enums\MessageType;
use App\Domains\Messaging\Events\MessageSent;
use App\Domains\Messaging\Models\Conversation;
use App\Domains\Messaging\Models\Message;
use App\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /** Найти или создать личный диалог между двумя пользователями. */
    public function findOrCreatePrivate(User $author, User $recipient): Conversation
    {
        $existing = Conversation::query()
            ->where('type', ConversationType::Private->value)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $author->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $recipient->id))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($author, $recipient): Conversation {
            $conversation = Conversation::create([
                'type' => ConversationType::Private->value,
                'created_by' => $author->id,
            ]);

            foreach ([$author, $recipient] as $user) {
                $conversation->participants()->create([
                    'user_id' => $user->id,
                    'joined_at' => now(),
                ]);
            }

            return $conversation;
        });
    }

    public function sendMessage(Conversation $conversation, User $author, string $body, ?int $replyToId = null): Message
    {
        $message = DB::transaction(function () use ($conversation, $author, $body, $replyToId): Message {
            $message = $conversation->messages()->create([
                'user_id' => $author->id,
                'type' => MessageType::Text->value,
                'body' => $body,
                'reply_to_id' => $replyToId,
            ]);

            $conversation->forceFill([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ])->save();

            $conversation->participants()
                ->where('user_id', $author->id)
                ->update(['last_read_message_id' => $message->id]);

            return $message;
        });

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }
}
