<?php

declare(strict_types=1);

namespace App\Domains\Support\Http\Controllers;

use App\Domains\Support\Models\LegalDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class LegalDocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $documents = LegalDocument::current()
            ->get(['id', 'type', 'version', 'title', 'published_at']);

        return response()->json(['data' => $documents]);
    }

    public function show(string $type): JsonResponse
    {
        $document = LegalDocument::current()->where('type', $type)->firstOrFail();

        return response()->json([
            'data' => $document->only(['id', 'type', 'version', 'title', 'content', 'published_at']),
        ]);
    }
}
