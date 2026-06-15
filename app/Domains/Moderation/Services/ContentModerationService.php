<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Services;

use App\Domains\Moderation\Enums\ContentRuleAction;
use App\Domains\Moderation\Enums\ContentRuleType;
use App\Domains\Moderation\Models\ContentRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentModerationService
{
    /**
     * Проверяет текст на стоп-слова и запрещённые ссылки.
     *
     * @return array{action: 'pass'|'flag'|'block', matches: array<int, string>}
     */
    public function check(?string ...$texts): array
    {
        $haystack = Str::lower(trim(implode(' ', array_filter($texts))));

        if ($haystack === '') {
            return ['action' => 'pass', 'matches' => []];
        }

        $rules = $this->activeRules();
        $matches = [];
        $shouldBlock = false;

        foreach ($rules as $rule) {
            $value = Str::lower((string) $rule['value']);

            if ($value === '') {
                continue;
            }

            $hit = $rule['type'] === ContentRuleType::BannedLink->value
                ? str_contains($haystack, $value)
                : $this->matchesWord($haystack, $value);

            if ($hit) {
                $matches[] = $rule['value'];

                if ($rule['action'] === ContentRuleAction::Block->value) {
                    $shouldBlock = true;
                }
            }
        }

        if ($matches === []) {
            return ['action' => 'pass', 'matches' => []];
        }

        return [
            'action' => $shouldBlock ? 'block' : 'flag',
            'matches' => array_values(array_unique($matches)),
        ];
    }

    private function matchesWord(string $haystack, string $word): bool
    {
        return (bool) preg_match('/\b'.preg_quote($word, '/').'\b/u', $haystack)
            || str_contains($haystack, $word);
    }

    /**
     * @return array<int, array{type: string, value: string, action: string}>
     */
    private function activeRules(): array
    {
        return Cache::remember('content_rules.active', now()->addMinutes(10), function (): array {
            return ContentRule::query()
                ->where('is_active', true)
                ->get(['type', 'value', 'action'])
                ->map(fn (ContentRule $rule): array => [
                    'type' => $rule->getRawOriginal('type'),
                    'value' => $rule->value,
                    'action' => $rule->getRawOriginal('action'),
                ])
                ->all();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('content_rules.active');
    }
}
