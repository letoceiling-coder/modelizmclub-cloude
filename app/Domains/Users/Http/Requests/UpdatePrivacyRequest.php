<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrivacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'privacy' => ['required', 'array'],
            'privacy.show_email' => ['sometimes', 'boolean'],
            'privacy.show_phone' => ['sometimes', 'boolean'],
            'privacy.show_birthdate' => ['sometimes', 'boolean'],
            'privacy.allow_messages' => ['sometimes', 'boolean'],
        ];
    }
}
