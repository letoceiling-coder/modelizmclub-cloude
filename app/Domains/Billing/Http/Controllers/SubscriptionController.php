<?php

declare(strict_types=1);

namespace App\Domains\Billing\Http\Controllers;

use App\Domains\Billing\Enums\PaymentProvider;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\Enums\SubscriptionStatus;
use App\Domains\Billing\Models\Payment;
use App\Domains\Billing\Models\Plan;
use App\Domains\Billing\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $subscriptions = $request->user()->subscriptions()
            ->with('plan')
            ->latest()
            ->get(['id', 'plan_id', 'status', 'starts_at', 'ends_at', 'auto_renew']);

        return response()->json(['data' => $subscriptions]);
    }

    /**
     * Каркас оформления подписки (Этап 2). Создаёт черновик подписки и платёж
     * в статусе «ожидает оплаты»; интеграция с провайдером (ВТБ/ЮKassa)
     * и обработка вебхуков — следующий шаг.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'provider' => ['required', Rule::in([PaymentProvider::Vtb->value, PaymentProvider::YooKassa->value])],
            'auto_renew' => ['boolean'],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);

        $result = DB::transaction(function () use ($request, $plan, $data): array {
            $subscription = Subscription::create([
                'user_id' => $request->user()->id,
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Pending->value,
                'auto_renew' => $data['auto_renew'] ?? false,
            ]);

            $payment = Payment::create([
                'user_id' => $request->user()->id,
                'payable_id' => $subscription->id,
                'payable_type' => 'subscription',
                'amount' => $plan->price,
                'currency' => 'RUB',
                'status' => PaymentStatus::Pending->value,
                'provider' => $data['provider'],
            ]);

            $subscription->forceFill(['payment_id' => $payment->id])->save();

            return ['subscription' => $subscription, 'payment' => $payment];
        });

        return response()->json([
            'message' => 'Подписка создана, требуется оплата.',
            'subscription_id' => $result['subscription']->id,
            'payment_id' => $result['payment']->id,
            'payment_status' => PaymentStatus::Pending->value,
            'gateway' => [
                'provider' => $data['provider'],
                'redirect_url' => null,
                'note' => 'Интеграция платёжного провайдера будет завершена на этапе 2.',
            ],
        ], 202);
    }
}
