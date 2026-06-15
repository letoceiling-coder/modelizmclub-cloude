<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Controllers;

use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Http\Requests\StorePostRequest;
use App\Domains\Feed\Http\Requests\UpdatePostRequest;
use App\Domains\Feed\Http\Resources\PostResource;
use App\Domains\Feed\Models\Post;
use App\Domains\Feed\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $perPage = min((int) $request->integer('per_page', config('modelizm.pagination.feed_per_page', 15)), 100);

        $posts = QueryBuilder::for(Post::published())
            ->allowedFilters(
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('community_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::callback('tag', function ($query, $value): void {
                    $query->whereHas('tags', fn ($q) => $q->where('slug', $value));
                }),
            )
            ->allowedSorts(
                AllowedSort::field('published_at'),
                AllowedSort::field('likes_count'),
                AllowedSort::field('comments_count'),
            )
            ->defaultSort('-published_at')
            ->with($this->withRelations($user))
            ->cursorPaginate($perPage)
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function show(Request $request, Post $post): PostResource
    {
        $this->authorize('view', $post);

        $post->increment('views_count');
        $post->load($this->withRelations($request->user()));

        return PostResource::make($post);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $this->authorize('create', Post::class);

        $post = $this->service->create(
            $request->user(),
            $request->validated(),
            $request->file('photos', []),
            $request->file('video'),
        );

        return PostResource::make($post->load($this->withRelations($request->user())))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);

        $post = $this->service->update(
            $post,
            $request->validated(),
            $request->file('photos', []),
            $request->file('video'),
        );

        return PostResource::make($post->load($this->withRelations($request->user())));
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();

        return response()->json(['message' => 'Пост удалён.']);
    }

    public function pin(Request $request, Post $post): PostResource
    {
        $this->authorize('pin', $post);
        $post->forceFill(['is_pinned' => true, 'pinned_at' => now()])->save();

        return PostResource::make($post->load($this->withRelations($request->user())));
    }

    public function unpin(Request $request, Post $post): PostResource
    {
        $this->authorize('pin', $post);
        $post->forceFill(['is_pinned' => false, 'pinned_at' => null])->save();

        return PostResource::make($post->load($this->withRelations($request->user())));
    }

    /**
     * @return array<string, mixed>
     */
    private function withRelations(?\App\Domains\Users\Models\User $user): array
    {
        $relations = ['user', 'category', 'community', 'tags', 'media'];

        if ($user !== null) {
            $relations['reactions'] = fn ($q) => $q->where('user_id', $user->id);
            $relations['bookmarks'] = fn ($q) => $q->where('user_id', $user->id);
        }

        return $relations;
    }
}
