<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Models;

use App\Domains\Moderation\Enums\ContentRuleAction;
use App\Domains\Moderation\Enums\ContentRuleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'action',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentRuleType::class,
            'action' => ContentRuleAction::class,
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, ContentRuleType $type): Builder
    {
        return $query->where('type', $type->value);
    }
}
