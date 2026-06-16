<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/** Проверка доступности API (healthcheck). */
class PingController extends Controller
{
    /**
     * @response array{status: string, service: string, version: string}
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'modelizm-api',
            'version' => 'v1',
        ]);
    }
}
