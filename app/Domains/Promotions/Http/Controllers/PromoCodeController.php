<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Http\Controllers;

use App\Domains\Promotions\Models\PromoCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /** Проверить промокод перед оплатой (Этап 2). */
    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        $promo = PromoCode::active()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($data['code'])])
            ->first();

        if ($promo === null || $promo->isExhausted()) {
            return response()->json([
                'valid' => false,
                'message' => 'Промокод недействителен или исчерпан.',
            ], 422);
        }

        $alreadyUsed = $promo->max_per_user !== null
            && $promo->redemptions()->where('user_id', $request->user()->id)->count() >= $promo->max_per_user;

        if ($alreadyUsed) {
            return response()->json([
                'valid' => false,
                'message' => 'Промокод уже использован.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'code' => $promo->code,
            'type' => $promo->type?->value,
            'value' => $promo->value,
            'free_ads_count' => $promo->free_ads_count,
            'duration_days' => $promo->duration_days,
        ]);
    }
}
