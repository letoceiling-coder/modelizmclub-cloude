<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Controllers;

use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Moderation\Http\Resources\ModerationItemResource;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Moderation\Services\ModerationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ModerationController extends Controller
{
    public function __construct(
        private readonly ModerationService $service,
    ) {}

    /** Очередь модерации: поддерживает фильтр по типу контента и статусу. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ModerationItem::query()
            ->with(['moderatable', 'submitter'])
            ->latest('submitted_at');

        $status = $request->string('status')->toString() ?: ModerationStatus::Pending->value;
        $query->where('status', $status);

        if ($type = $request->string('type')->toString()) {
            $query->where('moderatable_type', $type);
        }

        return ModerationItemResource::collection($query->paginate(30));
    }

    public function show(ModerationItem $item): ModerationItemResource
    {
        return ModerationItemResource::make($item->load(['moderatable', 'submitter', 'reviewer']));
    }

    public function approve(Request $request, ModerationItem $item): ModerationItemResource
    {
        return $this->decide($request, $item, ModerationStatus::Approved);
    }

    public function reject(Request $request, ModerationItem $item): ModerationItemResource
    {
        return $this->decide($request, $item, ModerationStatus::Rejected);
    }

    public function needsRevision(Request $request, ModerationItem $item): ModerationItemResource
    {
        return $this->decide($request, $item, ModerationStatus::NeedsRevision);
    }

    private function decide(Request $request, ModerationItem $item, ModerationStatus $decision): ModerationItemResource
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = $this->service->decide($item, $request->user(), $decision, $data['reason'] ?? null);

        return ModerationItemResource::make($item->load(['moderatable', 'submitter', 'reviewer']));
    }
}
