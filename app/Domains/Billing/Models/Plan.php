<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\PlanPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'period',
        'photo_limit',
        'ad_priority',
        'free_ads_count',
        'discount_percent',
        'badge',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'period' => PlanPeriod::class,
            'price' => 'decimal:2',
            'photo_limit' => 'integer',
            'ad_priority' => 'integer',
            'free_ads_count' => 'integer',
            'discount_percent' => 'integer',
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'feature_plan')
            ->withPivot(['enabled', 'value']);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isFree(): bool
    {
        return (float) $this->price === 0.0;
    }
}
