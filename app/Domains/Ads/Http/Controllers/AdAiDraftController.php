<?php

declare(strict_types=1);

namespace App\Domains\Ads\Http\Controllers;

use App\Domains\Ads\Enums\AiDraftStatus;
use App\Domains\Ads\Models\AdAiDraft;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdAiDraftController extends Controller
{
    /**
     * ИИ-помощник для черновика объявления (Этап 2). Сейчас — каркас:
     * сохраняет вводные и ставит задачу в очередь; генерация описания
     * подключается отдельной job'ой к ИИ-провайдеру.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'keywords' => ['nullable', 'array'],
            'keywords.*' => ['string', 'max:50'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $draft = AdAiDraft::create([
            'user_id' => $request->user()->id,
            'input' => $data,
            'suggested_category_id' => $data['category_id'] ?? null,
            'status' => AiDraftStatus::Pending->value,
        ]);

        return response()->json([
            'data' => [
                'id' => $draft->id,
                'status' => $draft->status->value,
            ],
            'message' => 'Запрос принят. Генерация описания будет выполнена в фоне.',
        ], 202);
    }

    public function show(Request $request, AdAiDraft $draft): JsonResponse
    {
        abort_unless($draft->user_id === $request->user()->id, 403);

        return response()->json([
            'data' => [
                'id' => $draft->id,
                'status' => $draft->status?->value,
                'generated_description' => $draft->generated_description,
                'suggested_category_id' => $draft->suggested_category_id,
            ],
        ]);
    }
}
