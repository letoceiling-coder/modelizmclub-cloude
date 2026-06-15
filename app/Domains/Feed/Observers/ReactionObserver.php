<?php

declare(strict_types=1);

namespace App\Domains\Feed\Observers;

use App\Domains\Feed\Models\Reaction;

class ReactionObserver
{
    public function created(Reaction $reaction): void
    {
        $reaction->reactable?->increment('likes_count');
    }

    public function deleted(Reaction $reaction): void
    {
        $reaction->reactable?->decrement('likes_count');
    }
}
