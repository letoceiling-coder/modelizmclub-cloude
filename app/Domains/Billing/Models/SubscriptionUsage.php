<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'free_ads_used',
        'period_index',
        'period_started_at',
    ];

    protected function casts(): array
    {
        return [
            'free_ads_used' => 'integer',
            'period_index' => 'integer',
            'period_started_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
