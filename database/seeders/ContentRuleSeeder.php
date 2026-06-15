<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Moderation\Enums\ContentRuleAction;
use App\Domains\Moderation\Enums\ContentRuleType;
use App\Domains\Moderation\Models\ContentRule;
use Illuminate\Database\Seeder;

class ContentRuleSeeder extends Seeder
{
    public function run(): void
    {
        $stopWords = ['казино', 'ставки', 'порно', 'наркотики', 'spam'];
        $bannedLinks = ['bit.ly', 'tinyurl.com', 't.me/joinchat'];

        foreach ($stopWords as $word) {
            ContentRule::query()->updateOrCreate(
                ['type' => ContentRuleType::StopWord->value, 'value' => $word],
                ['action' => ContentRuleAction::Block->value, 'is_active' => true],
            );
        }

        foreach ($bannedLinks as $link) {
            ContentRule::query()->updateOrCreate(
                ['type' => ContentRuleType::BannedLink->value, 'value' => $link],
                ['action' => ContentRuleAction::Flag->value, 'is_active' => true],
            );
        }
    }
}
