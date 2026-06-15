<?php

declare(strict_types=1);

namespace App\Domains\Support\Http\Controllers;

use App\Domains\Support\Models\KbArticle;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KbArticleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $articles = KbArticle::published()
            ->search($request->string('q')->toString())
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->orderBy('position')
            ->get(['id', 'category', 'title', 'slug', 'excerpt', 'position']);

        return response()->json(['data' => $articles]);
    }

    public function show(KbArticle $kbArticle): JsonResponse
    {
        abort_unless($kbArticle->is_published, 404);
        $kbArticle->increment('views_count');

        return response()->json([
            'data' => $kbArticle->only(['id', 'category', 'title', 'slug', 'excerpt', 'body']),
        ]);
    }
}
