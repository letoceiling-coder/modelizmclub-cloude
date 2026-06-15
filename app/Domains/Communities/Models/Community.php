<?php

declare(strict_types=1);

namespace App\Domains\Communities\Models;

use App\Domains\Catalog\Models\Category;
use App\Domains\Communities\Enums\CommunityStatus;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Community extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'category_id',
        'name',
        'slug',
        'description',
        'avatar_path',
        'cover_path',
        'video_path',
        'status',
        'blocked_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommunityStatus::class,
            'members_count' => 'integer',
            'posts_count' => 'integer',
            'blocked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Community $community): void {
            $community->slug ??= Str::slug($community->name).'-'.Str::lower(Str::random(5));
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(900)->height(900);
    }

    // --- Отношения ----------------------------------------------------------

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CommunitySection::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function moderation(): MorphMany
    {
        return $this->morphMany(ModerationItem::class, 'moderatable');
    }

    // --- Скопы --------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CommunityStatus::Active->value);
    }

    public function scopeOfCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
