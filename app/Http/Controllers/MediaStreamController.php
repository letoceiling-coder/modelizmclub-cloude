<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Отдача медиа с S3 через поддомен img.* (бакет может быть приватным).
 * URL формируются как {MEDIA_URL}/{path}, path — ключ объекта в бакете.
 */
class MediaStreamController extends Controller
{
    public function __invoke(Request $request, string $path): StreamedResponse
    {
        $diskName = (string) config('uploads.disk', config('media-library.disk_name', 'public'));
        $prefix = trim((string) config('media-library.prefix', ''), '/');

        if ($prefix !== '' && ! str_starts_with($path, $prefix.'/')) {
            abort(404);
        }

        // Защита от path traversal
        if (str_contains($path, '..')) {
            abort(404);
        }

        $disk = Storage::disk($diskName);

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->response($path, null, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800, immutable',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
