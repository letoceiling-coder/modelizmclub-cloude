<?php

declare(strict_types=1);

namespace App\Domains\Ads\Models;

use App\Domains\Ads\Enums\AiDraftStatus;
use App\Domains\Catalog\Models\Category;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAiDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ad_id',
        'input',
        'generated_description',
        'suggested_category_id',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'status' => AiDraftStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function suggestedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'suggested_category_id');
    }
}
