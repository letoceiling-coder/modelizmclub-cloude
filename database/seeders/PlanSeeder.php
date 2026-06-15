<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Billing\Enums\PlanPeriod;
use App\Domains\Billing\Models\Feature;
use App\Domains\Billing\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'photos' => 'Фото в объявлении',
            'ad_priority' => 'Приоритет в выдаче',
            'free_ads' => 'Бесплатные объявления',
            'no_ads' => 'Без рекламы',
            'badge' => 'Значок подписчика',
            'support' => 'Приоритетная поддержка',
        ];

        $featureModels = [];
        $pos = 0;
        foreach ($features as $key => $label) {
            $featureModels[$key] = Feature::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $label, 'position' => $pos++],
            );
        }

        $plans = [
            [
                'name' => 'Базовый',
                'slug' => 'free',
                'description' => 'Бесплатный доступ к платформе.',
                'price' => 0,
                'period' => PlanPeriod::Lifetime->value,
                'photo_limit' => 5,
                'ad_priority' => 0,
                'free_ads_count' => 1,
                'discount_percent' => 0,
                'badge' => null,
                'position' => 0,
            ],
            [
                'name' => 'Премиум',
                'slug' => 'premium',
                'description' => 'Больше фото, приоритет объявлений и значок подписчика.',
                'price' => 299,
                'period' => PlanPeriod::Month->value,
                'photo_limit' => 10,
                'ad_priority' => 5,
                'free_ads_count' => 10,
                'discount_percent' => 0,
                'badge' => 'premium',
                'position' => 1,
            ],
            [
                'name' => 'Премиум год',
                'slug' => 'premium-year',
                'description' => 'Все возможности «Премиум» со скидкой при оплате за год.',
                'price' => 2690,
                'period' => PlanPeriod::Year->value,
                'photo_limit' => 10,
                'ad_priority' => 5,
                'free_ads_count' => 10,
                'discount_percent' => 25,
                'badge' => 'premium',
                'position' => 2,
            ],
        ];

        foreach ($plans as $data) {
            $plan = Plan::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true]),
            );

            $plan->features()->syncWithoutDetaching(
                collect($featureModels)->mapWithKeys(fn ($f) => [
                    $f->id => ['enabled' => true],
                ])->all(),
            );
        }
    }
}
