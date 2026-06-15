<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Controllers;

use App\Domains\Feed\Enums\CommentStatus;
use App\Domains\Feed\Http\Requests\StoreCommentRequest;
use App\Domains\Feed\Http\Resources\CommentResource;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Services\ContentModerationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function __construct(
        private readonly ContentModerationService $moderation,
    ) {}

    /** Корневые комментарии поста (ветки подгружаются отдельно). */
    public function index(Post $post): AnonymousResourceCollection
    {
        $comments = $post->comments()
            ->published()
            ->roots()
            ->with('user')
            ->orderByDesc('id')
            ->cursorPaginate(20);

        return CommentResource::collection($comments);
    }

    /** Ответы на комментарий (вложенная ветка). */
    public function replies(Comment $comment): AnonymousResourceCollection
    {
        $replies = $comment->replies()
            ->published()
            ->with('user')
            ->orderBy('id')
            ->cursorPaginate(20);

        return CommentResource::collection($replies);
    }

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $this->authorize('create', Comment::class);

        $verdict = $this->moderation->check($request->string('body')->toString());

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $request->input('parent_id'),
            'body' => $request->string('body')->toString(),
            'status' => $verdict['action'] === 'block'
                ? CommentStatus::Pending->value
                : CommentStatus::Published->value,
        ]);

        return CommentResource::make($comment->load('user'))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(['message' => 'Комментарий удалён.']);
    }
}
