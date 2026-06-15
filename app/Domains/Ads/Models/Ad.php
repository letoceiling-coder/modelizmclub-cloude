<?php

declare(strict_types=1);

namespace App\Domains\Ads\Models;

use App\Domains\Ads\Enums\AdCondition;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\City;
use App\Domains\Catalog\Models\DeliveryMethod;
use App\Domains\Feed\Models\Bookmark;
use App\Domains\Moderation\Models\ModerationItem;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Ad extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'subcategory_id',
        'city_id',
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'condition',
        'status',
        'contact_via_chat',
        'contact_phone',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AdStatus::class,
            'condition' => AdCondition::class,
            'price' => 'decimal:2',
            'contact_via_chat' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'moderated_at' => 'datetime',
            'views_count' => 'integer',
            'favorites_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(400);
        $this->addMediaConversion('medium')->width(1080);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function deliveryMethods(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryMethod::class, 'ad_delivery_methods');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function moderation(): MorphMany
    {
        return $this->morphMany(ModerationItem::class, 'moderatable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', AdStatus::Published->value)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
