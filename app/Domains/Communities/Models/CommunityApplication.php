<?php

declare(strict_types=1);

namespace App\Domains\Communities\Models;

use App\Domains\Catalog\Models\Category;
use App\Domains\Communities\Enums\CommunityApplicationStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'community_id',
        'proposed_name',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommunityApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommunityApplicationStatus::Pending->value);
    }
}
