<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Controllers;

use App\Domains\Feed\Http\Resources\PostResource;
use App\Domains\Feed\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookmarkController extends Controller
{
    public function toggle(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $existing = $post->bookmarks()->where('user_id', $user->id)->first();

        if ($existing !== null) {
            $existing->delete();
            $bookmarked = false;
        } else {
            $post->bookmarks()->create(['user_id' => $user->id]);
            $bookmarked = true;
        }

        return response()->json(['bookmarked' => $bookmarked]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $posts = Post::query()
            ->whereHas('bookmarks', fn ($q) => $q->where('user_id', $user->id))
            ->with(['user', 'category', 'community', 'tags', 'media'])
            ->latest()
            ->cursorPaginate(20);

        return PostResource::collection($posts);
    }
}
