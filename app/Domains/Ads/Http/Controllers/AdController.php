<?php

declare(strict_types=1);

namespace App\Domains\Ads\Http\Controllers;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Http\Resources\AdResource;
use App\Domains\Ads\Models\Ad;
use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Moderation\Services\ContentModerationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class AdController extends Controller
{
    public function __construct(
        private readonly ContentModerationService $moderation,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $ads = QueryBuilder::for(Ad::published())
            ->allowedFilters(
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('city_id'),
                AllowedFilter::exact('condition'),
                AllowedFilter::partial('title'),
            )
            ->allowedSorts(
                AllowedSort::field('price'),
                AllowedSort::field('published_at'),
            )
            ->defaultSort('-published_at')
            ->with(['user', 'category', 'city', 'media'])
            ->cursorPaginate(min((int) $request->integer('per_page', 20), 100))
            ->withQueryString();

        return AdResource::collection($ads);
    }

    public function show(Ad $ad): AdResource
    {
        abort_unless($ad->status === AdStatus::Published, 404);
        $ad->increment('views_count');

        return AdResource::make($ad->load(['user', 'category', 'city', 'media']));
    }

    /**
     * Создание объявления. Бесплатные объявления и лимиты фото по тарифу,
     * приём оплаты за платные размещения — Этап 2 (биллинг).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['nullable', 'string'],
            'contact_via_chat' => ['boolean'],
        ]);

        $verdict = $this->moderation->check($data['title'], $data['description']);

        $ad = new Ad($data);
        $ad->user_id = $request->user()->id;
        $ad->slug = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $ad->currency = 'RUB';
        $ad->status = AdStatus::Pending->value;
        $ad->save();

        if ($verdict['action'] !== 'pass') {
            $ad->moderation()->create([
                'status' => ModerationStatus::Pending->value,
                'submitted_by' => $request->user()->id,
                'submitted_at' => now(),
                'flags' => $verdict['matches'],
            ]);
        }

        return AdResource::make($ad->load(['user', 'category', 'city']))
            ->response()
            ->setStatusCode(201);
    }
}
