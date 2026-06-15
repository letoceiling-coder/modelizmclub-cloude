<?php

declare(strict_types=1);

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\PaymentProvider;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payable_id',
        'payable_type',
        'amount',
        'bonus_amount',
        'currency',
        'status',
        'provider',
        'provider_payment_id',
        'promo_code_id',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'provider' => PaymentProvider::class,
            'amount' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(PaymentRefund::class);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PaymentStatus::Paid->value,
            PaymentStatus::PaidByBonus->value,
            PaymentStatus::PromoApplied->value,
        ]);
    }
}
