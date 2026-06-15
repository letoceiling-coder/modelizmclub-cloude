<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Http\Controllers;

use App\Domains\Catalog\Http\Resources\CityResource;
use App\Domains\Catalog\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $cities = City::active()
            ->search($request->string('q')->toString())
            ->orderBy('name')
            ->limit(50)
            ->get();

        return CityResource::collection($cities);
    }
}
