<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Controllers;

use App\Domains\Feed\Enums\ReactionType;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function togglePost(Request $request, Post $post): JsonResponse
    {
        return $this->toggle($request, $post);
    }

    public function toggleComment(Request $request, Comment $comment): JsonResponse
    {
        return $this->toggle($request, $comment);
    }

    private function toggle(Request $request, Model $target): JsonResponse
    {
        $data = $request->validate([
            'type' => ['nullable', Rule::in(ReactionType::values())],
        ]);
        $type = $data['type'] ?? ReactionType::Like->value;
        $user = $request->user();

        $existing = $target->reactions()->where('user_id', $user->id)->first();

        if ($existing !== null && $existing->type->value === $type) {
            $existing->delete();
            $reacted = false;
        } else {
            $existing?->delete();
            $target->reactions()->create([
                'user_id' => $user->id,
                'type' => $type,
            ]);
            $reacted = true;
        }

        return response()->json([
            'reacted' => $reacted,
            'type' => $type,
            'likes_count' => $target->fresh()->likes_count,
        ]);
    }
}
