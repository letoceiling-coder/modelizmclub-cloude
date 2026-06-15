<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Controllers;

use App\Domains\Communities\Enums\CommunityApplicationStatus;
use App\Domains\Communities\Http\Requests\StoreCommunityApplicationRequest;
use App\Domains\Communities\Http\Resources\CommunityApplicationResource;
use App\Domains\Communities\Http\Resources\CommunityResource;
use App\Domains\Communities\Models\CommunityApplication;
use App\Domains\Communities\Services\CommunityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommunityApplicationController extends Controller
{
    public function __construct(
        private readonly CommunityService $service,
    ) {}

    /** Список заявок: свои — обычному пользователю, все — модератору. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CommunityApplication::query()->with(['user', 'category'])->latest();

        if (! $request->user()->can('communities.moderate')) {
            $query->where('user_id', $request->user()->id);
        } elseif ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return CommunityApplicationResource::collection($query->paginate(20));
    }

    public function store(StoreCommunityApplicationRequest $request): JsonResponse
    {
        $application = CommunityApplication::create([
            'user_id' => $request->user()->id,
            'status' => CommunityApplicationStatus::Pending->value,
            ...$request->validated(),
        ]);

        return CommunityApplicationResource::make($application->load(['user', 'category']))
            ->response()
            ->setStatusCode(201);
    }

    public function approve(Request $request, CommunityApplication $application): JsonResponse
    {
        abort_unless($request->user()->can('communities.moderate'), 403);

        $community = $this->service->approveApplication($application, $request->user());

        return CommunityResource::make($community->load(['owner', 'category']))
            ->response()
            ->setStatusCode(201);
    }

    public function reject(Request $request, CommunityApplication $application): CommunityApplicationResource
    {
        abort_unless($request->user()->can('communities.moderate'), 403);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $application = $this->service->rejectApplication($application, $request->user(), $data['reason'] ?? null);

        return CommunityApplicationResource::make($application->load(['user', 'category']));
    }
}
