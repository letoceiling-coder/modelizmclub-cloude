<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Http\Controllers;

use App\Domains\Promotions\Models\Banner;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $banners = Banner::activeNow()
            ->when($request->filled('placement'), fn ($q) => $q->placement($request->string('placement')->toString()))
            ->orderBy('position')
            ->get(['id', 'title', 'image_path', 'link_url', 'text', 'placement', 'position']);

        return response()->json(['data' => $banners]);
    }

    public function click(Banner $banner): JsonResponse
    {
        $banner->increment('clicks_count');

        return response()->json(['link_url' => $banner->link_url]);
    }
}
