<?php

declare(strict_types=1);

namespace App\Domains\Promotions\Models;

use App\Domains\Promotions\Enums\BonusTransactionType;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BonusTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'source',
        'reference_id',
        'reference_type',
        'comment',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => BonusTransactionType::class,
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
