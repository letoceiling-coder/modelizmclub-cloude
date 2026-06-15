<?php

declare(strict_types=1);

namespace App\Domains\Users\Models;

use App\Domains\Users\Enums\ConsentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'version',
        'accepted_at',
        'ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'type' => ConsentType::class,
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
