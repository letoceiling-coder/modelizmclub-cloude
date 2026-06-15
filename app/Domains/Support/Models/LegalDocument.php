<?php

declare(strict_types=1);

namespace App\Domains\Support\Models;

use App\Domains\Support\Enums\LegalDocumentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'version',
        'title',
        'content',
        'is_current',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => LegalDocumentType::class,
            'is_current' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }
}
