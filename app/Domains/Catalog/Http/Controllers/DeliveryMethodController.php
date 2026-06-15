<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Models\DeliveryMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DeliveryMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = DeliveryMethod::active()
            ->orderBy('position')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $methods]);
    }
}
