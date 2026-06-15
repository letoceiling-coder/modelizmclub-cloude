<?php

declare(strict_types=1);

namespace App\Domains\Feed\Models;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Tag;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunitySection;
use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'community_id',
        'community_section_id',
        'category_id',
        'repost_of_id',
        'title',
        'body',
        'status',
        'is_pinned',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'published_at' => 'datetime',
            'moderated_at' => 'datetime',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'reposts_count' => 'integer',
            'views_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('video')
            ->singleFile()
            ->acceptsMimeTypes(['video/mp4', 'video/webm', 'video/quicktime']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(400)->performOnCollections('photos');
        $this->addMediaConversion('medium')->width(1080)->performOnCollections('photos');
    }

    // --- Отношения ----------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->user();
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CommunitySection::class, 'community_section_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function repostOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repost_of_id');
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(self::class, 'repost_of_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function moderation(): MorphMany
    {
        return $this->morphMany(ModerationItem::class, 'moderatable');
    }

    // --- Скопы --------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published->value)
            ->whereNotNull('published_at');
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByCommunity(Builder $query, int $communityId): Builder
    {
        return $query->where('community_id', $communityId);
    }

    public function scopePinnedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')->orderByDesc('published_at');
    }

    /**
     * Лента: только опубликованные посты, опционально по подпискам пользователя
     * (его сообщества и авторы) — объявления в ленту НЕ попадают.
     */
    public function scopeForFeed(Builder $query, ?User $user = null, bool $onlySubscriptions = false): Builder
    {
        $query->published();

        if ($onlySubscriptions && $user !== null) {
            $communityIds = $user->communities()->pluck('communities.id');

            $query->where(function (Builder $q) use ($communityIds, $user): void {
                $q->whereIn('community_id', $communityIds)
                    ->orWhere('user_id', $user->id);
            });
        }

        return $query;
    }

    /**
     * Видимость: автор и модераторы видят свои/любые посты,
     * остальные — только опубликованные.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user !== null && $user->isModerator()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('status', PostStatus::Published->value);

            if ($user !== null) {
                $q->orWhere('user_id', $user->id);
            }
        });
    }
}
