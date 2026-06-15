<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Controllers;

use App\Domains\Communities\Http\Requests\UpdateCommunityRequest;
use App\Domains\Communities\Http\Resources\CommunityResource;
use App\Domains\Communities\Models\Community;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class CommunityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $communities = QueryBuilder::for(Community::active())
            ->allowedFilters(
                AllowedFilter::exact('category_id'),
                AllowedFilter::partial('name'),
            )
            ->allowedSorts(
                AllowedSort::field('members_count'),
                AllowedSort::field('posts_count'),
                AllowedSort::field('created_at'),
            )
            ->defaultSort('-members_count')
            ->with(['owner', 'category'])
            ->paginate(min((int) $request->integer('per_page', 20), 100))
            ->withQueryString();

        return CommunityResource::collection($communities);
    }

    public function show(Request $request, Community $community): CommunityResource
    {
        $relations = ['owner', 'category', 'sections'];

        if ($user = $request->user()) {
            $relations['members'] = fn ($q) => $q->where('user_id', $user->id);
        }

        return CommunityResource::make($community->load($relations));
    }

    public function update(UpdateCommunityRequest $request, Community $community): CommunityResource
    {
        $this->authorize('update', $community);

        $community->fill($request->safe()->except(['avatar', 'cover']));
        $community->save();

        if ($request->hasFile('avatar')) {
            $community->clearMediaCollection('avatar');
            $community->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        if ($request->hasFile('cover')) {
            $community->clearMediaCollection('cover');
            $community->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return CommunityResource::make($community->fresh(['owner', 'category', 'sections']));
    }

    public function destroy(Community $community): JsonResponse
    {
        $this->authorize('delete', $community);
        $community->delete();

        return response()->json(['message' => 'Сообщество удалено.']);
    }
}
