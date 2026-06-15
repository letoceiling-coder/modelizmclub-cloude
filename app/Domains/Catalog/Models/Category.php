<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Models;

use App\Domains\Catalog\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kalnoy\Nestedset\NodeTrait;

class Category extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;
    use NodeTrait;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'icon',
        'description',
        'position',
        'is_active',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_active' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function scopeOfType(Builder $query, CategoryType|string $type): Builder
    {
        return $query->where('type', $type instanceof CategoryType ? $type->value : $type);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeContent(Builder $query): Builder
    {
        return $this->scopeOfType($query, CategoryType::Content);
    }

    public function scopeCommunity(Builder $query): Builder
    {
        return $this->scopeOfType($query, CategoryType::Community);
    }

    public function scopeAd(Builder $query): Builder
    {
        return $this->scopeOfType($query, CategoryType::Ad);
    }

    /**
     * Дерево вложенного множества скоупим по типу справочника,
     * чтобы content/community/ad не пересекались.
     */
    protected function getScopeAttributes(): array
    {
        return ['type'];
    }
}
