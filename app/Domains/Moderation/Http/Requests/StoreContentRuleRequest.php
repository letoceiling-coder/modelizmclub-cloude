<?php

declare(strict_types=1);

namespace App\Domains\Moderation\Http\Requests;

use App\Domains\Moderation\Enums\ContentRuleAction;
use App\Domains\Moderation\Enums\ContentRuleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content_rules.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ContentRuleType::values())],
            'value' => ['required', 'string', 'max:255'],
            'action' => ['required', Rule::in(ContentRuleAction::values())],
            'is_active' => ['boolean'],
        ];
    }
}
