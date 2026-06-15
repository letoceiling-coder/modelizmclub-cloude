<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Controllers;

use App\Domains\Communities\Http\Requests\StoreCommunitySectionRequest;
use App\Domains\Communities\Http\Resources\CommunitySectionResource;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunitySection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommunitySectionController extends Controller
{
    public function index(Community $community): AnonymousResourceCollection
    {
        $sections = $community->sections()->orderBy('position')->get();

        return CommunitySectionResource::collection($sections);
    }

    public function store(StoreCommunitySectionRequest $request, Community $community): JsonResponse
    {
        $this->authorize('manageSections', $community);

        $section = $community->sections()->create($request->validated());

        return CommunitySectionResource::make($section)->response()->setStatusCode(201);
    }

    public function update(StoreCommunitySectionRequest $request, Community $community, CommunitySection $section): CommunitySectionResource
    {
        $this->authorize('manageSections', $community);
        abort_unless($section->community_id === $community->id, 404);

        $section->update($request->validated());

        return CommunitySectionResource::make($section);
    }

    public function destroy(Community $community, CommunitySection $section): JsonResponse
    {
        $this->authorize('manageSections', $community);
        abort_unless($section->community_id === $community->id, 404);

        $section->delete();

        return response()->json(['message' => 'Раздел удалён.']);
    }
}
