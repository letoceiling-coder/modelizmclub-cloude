<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Models;

use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'moderatable_id',
        'moderatable_type',
        'status',
        'submitted_by',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'decision',
        'reason',
        'flags',
    ];

    protected function casts(): array
    {
        return [
            'status' => ModerationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'flags' => 'array',
        ];
    }

    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ModerationStatus::Pending->value);
    }

    public function scopeOfType(Builder $query, string $morphType): Builder
    {
        return $query->where('moderatable_type', $morphType);
    }
}
