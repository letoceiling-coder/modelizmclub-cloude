<?php

declare(strict_types=1);

namespace App\Domains\Support\Http\Controllers;

use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\SupportTicket;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::query()
            ->where('user_id', $request->user()->id)
            ->withCount('messages')
            ->latest()
            ->get(['id', 'subject', 'status', 'created_at']);

        return response()->json(['data' => $tickets]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data): SupportTicket {
            $ticket = SupportTicket::create([
                'user_id' => $request->user()->id,
                'subject' => $data['subject'],
                'status' => SupportTicketStatus::Open->value,
            ]);

            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'is_operator' => false,
                'body' => $data['body'],
            ]);

            return $ticket;
        });

        return response()->json([
            'data' => $ticket->only(['id', 'subject', 'status']),
        ], 201);
    }

    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        abort_unless($ticket->user_id === $request->user()->id || $request->user()->can('support.handle'), 403);

        return response()->json([
            'data' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'status' => $ticket->status?->value,
                'messages' => $ticket->messages()->with('user:id,name')->get(['id', 'support_ticket_id', 'user_id', 'is_operator', 'body', 'created_at']),
            ],
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $isOperator = $request->user()->can('support.handle');
        abort_unless($ticket->user_id === $request->user()->id || $isOperator, 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'is_operator' => $isOperator,
            'body' => $data['body'],
        ]);

        if ($isOperator && $ticket->status === SupportTicketStatus::Open) {
            $ticket->update(['status' => SupportTicketStatus::InProgress->value]);
        }

        return response()->json(['data' => $message->only(['id', 'body', 'is_operator', 'created_at'])], 201);
    }
}
