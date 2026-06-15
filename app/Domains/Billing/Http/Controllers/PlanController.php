<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Billing\Http\Resources\PlanResource;
use App\Domains\Billing\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $plans = Plan::active()
            ->with('features')
            ->orderBy('position')
            ->get();

        return PlanResource::collection($plans);
    }
}
