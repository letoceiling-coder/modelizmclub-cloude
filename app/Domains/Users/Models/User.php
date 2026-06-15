<?php

declare(strict_types=1);

namespace App\Domains\Users\Models;

use App\Domains\Billing\Models\Payment;
use App\Domains\Billing\Models\Subscription;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\City;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunityMember;
use App\Domains\Feed\Models\Bookmark;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Feed\Models\Reaction;
use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\Gender;
use App\Domains\Users\Enums\Role;
use App\Domains\Users\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'avatar_path',
        'city_id',
        'bio',
        'birthdate',
        'gender',
        'settings',
        'privacy',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthdate' => 'date',
            'last_seen_at' => 'datetime',
            'rating' => 'integer',
            'settings' => 'array',
            'privacy' => 'array',
            'status' => UserStatus::class,
            'gender' => Gender::class,
        ];
    }

    // --- Атрибуты -----------------------------------------------------------

    /** Полный URL аватара (через настраиваемый медиа-диск/поддомен). */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $path = $this->attributes['avatar_path'] ?? null;

            if (! $path) {
                return null;
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            return Storage::disk(config('uploads.disk', 'public'))->url($path);
        });
    }

    // --- Отношения ----------------------------------------------------------

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_interests')->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ownedCommunities(): HasMany
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    public function communityMemberships(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'community_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function bonusAccount(): HasOne
    {
        return $this->hasOne(BonusAccount::class);
    }

    /** Пользователи, которых заблокировал этот пользователь. */
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'user_blocks', 'blocker_id', 'blocked_id')->withTimestamps();
    }

    // --- Скопы --------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // --- Помощники ----------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::Admin->value);
    }

    public function isModerator(): bool
    {
        return $this->hasAnyRole([Role::Admin->value, Role::Moderator->value]);
    }

    public function isSubscriber(): bool
    {
        return $this->subscriptions()
            ->where('status', \App\Domains\Billing\Enums\SubscriptionStatus::Active->value)
            ->where(function ($q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }
}
