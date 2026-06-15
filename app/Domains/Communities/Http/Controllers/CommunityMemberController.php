<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Controllers;

use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Http\Resources\CommunityMemberResource;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Services\CommunityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CommunityMemberController extends Controller
{
    public function __construct(
        private readonly CommunityService $service,
    ) {}

    public function index(Community $community): AnonymousResourceCollection
    {
        $members = $community->members()
            ->with('user')
            ->orderByDesc('role')
            ->paginate(50);

        return CommunityMemberResource::collection($members);
    }

    public function join(Request $request, Community $community): JsonResponse
    {
        $this->service->addMember($community, $request->user());

        return response()->json(['message' => 'Вы вступили в сообщество.'], 201);
    }

    public function leave(Request $request, Community $community): JsonResponse
    {
        abort_if($community->owner_id === $request->user()->id, 422, 'Владелец не может покинуть сообщество.');

        $community->members()->where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Вы покинули сообщество.']);
    }

    public function updateRole(Request $request, Community $community, int $user): CommunityMemberResource
    {
        $this->authorize('manageMembers', $community);

        $data = $request->validate([
            'role' => ['required', Rule::in(CommunityMemberRole::values())],
        ]);

        $member = $community->members()->where('user_id', $user)->firstOrFail();
        $member->update(['role' => $data['role']]);

        return CommunityMemberResource::make($member->load('user'));
    }

    public function remove(Request $request, Community $community, int $user): JsonResponse
    {
        $this->authorize('manageMembers', $community);
        abort_if($community->owner_id === $user, 422, 'Нельзя исключить владельца сообщества.');

        $community->members()->where('user_id', $user)->delete();

        return response()->json(['message' => 'Участник исключён.']);
    }
}
