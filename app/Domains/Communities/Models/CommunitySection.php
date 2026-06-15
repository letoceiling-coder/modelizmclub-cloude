<?php

declare(strict_types=1);

namespace App\Domains\Communities\Models;

use App\Domains\Feed\Models\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommunitySection extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'name',
        'slug',
        'description',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CommunitySection $section): void {
            $section->slug ??= Str::slug($section->name);
        });
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
