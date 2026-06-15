<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Http\Controllers;

use App\Domains\Messaging\Events\MessageRead;
use App\Domains\Messaging\Http\Resources\MessageResource;
use App\Domains\Messaging\Models\Conversation;
use App\Domains\Messaging\Services\MessagingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessagingService $service,
    ) {}

    public function index(Request $request, Conversation $conversation): AnonymousResourceCollection
    {
        $this->ensureParticipant($request, $conversation);

        $messages = $conversation->messages()
            ->with(['user', 'media'])
            ->orderByDesc('id')
            ->cursorPaginate(30);

        return MessageResource::collection($messages);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $this->ensureParticipant($request, $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'reply_to_id' => ['nullable', 'integer', 'exists:messages,id'],
        ]);

        $message = $this->service->sendMessage(
            $conversation,
            $request->user(),
            $data['body'],
            $data['reply_to_id'] ?? null,
        );

        return MessageResource::make($message->load('user'))->response()->setStatusCode(201);
    }

    /** Отметить диалог прочитанным до указанного сообщения. */
    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->ensureParticipant($request, $conversation);

        $data = $request->validate([
            'last_read_message_id' => ['required', 'integer', 'exists:messages,id'],
        ]);

        $conversation->participants()
            ->where('user_id', $request->user()->id)
            ->update(['last_read_message_id' => $data['last_read_message_id']]);

        broadcast(new MessageRead($conversation->id, $request->user()->id, $data['last_read_message_id']))->toOthers();

        return response()->json(['message' => 'Отмечено как прочитанное.']);
    }

    private function ensureParticipant(Request $request, Conversation $conversation): void
    {
        abort_unless(
            $conversation->participants()->where('user_id', $request->user()->id)->whereNull('left_at')->exists(),
            403,
            'Вы не участник этого диалога.',
        );
    }
}
