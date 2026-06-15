<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Controllers;

use App\Domains\Moderation\Http\Requests\StoreContentRuleRequest;
use App\Domains\Moderation\Http\Resources\ContentRuleResource;
use App\Domains\Moderation\Models\ContentRule;
use App\Domains\Moderation\Services\ContentModerationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContentRuleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('content_rules.manage'), 403);

        $rules = ContentRule::query()->orderBy('type')->orderBy('value')->paginate(100);

        return ContentRuleResource::collection($rules);
    }

    public function store(StoreContentRuleRequest $request): JsonResponse
    {
        $rule = ContentRule::create($request->validated());
        ContentModerationService::flushCache();

        return ContentRuleResource::make($rule)->response()->setStatusCode(201);
    }

    public function update(StoreContentRuleRequest $request, ContentRule $contentRule): ContentRuleResource
    {
        $contentRule->update($request->validated());
        ContentModerationService::flushCache();

        return ContentRuleResource::make($contentRule);
    }

    public function destroy(Request $request, ContentRule $contentRule): JsonResponse
    {
        abort_unless($request->user()->can('content_rules.manage'), 403);

        $contentRule->delete();
        ContentModerationService::flushCache();

        return response()->json(['message' => 'Правило удалено.']);
    }
}
