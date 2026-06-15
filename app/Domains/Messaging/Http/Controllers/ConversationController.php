<?php

declare(strict_types=1);

namespace App\Domains\Messaging\Http\Controllers;

use App\Domains\Messaging\Http\Resources\ConversationResource;
use App\Domains\Messaging\Models\Conversation;
use App\Domains\Messaging\Services\MessagingService;
use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationController extends Controller
{
    public function __construct(
        private readonly MessagingService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = Conversation::query()
            ->forUser($request->user())
            ->with(['lastMessage.user', 'participants.user'])
            ->orderByDesc('last_message_at')
            ->cursorPaginate(20);

        return ConversationResource::collection($conversations);
    }

    public function show(Request $request, Conversation $conversation): ConversationResource
    {
        $this->ensureParticipant($request, $conversation);

        return ConversationResource::make($conversation->load(['participants.user', 'lastMessage.user']));
    }

    /** Открыть/создать личный диалог с пользователем. */
    public function store(Request $request): ConversationResource
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id', 'different:'.$request->user()->id],
        ]);

        $recipient = User::findOrFail($data['recipient_id']);
        $conversation = $this->service->findOrCreatePrivate($request->user(), $recipient);

        return ConversationResource::make($conversation->load(['participants.user']));
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
