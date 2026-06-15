<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncInterestsRequest extends FormRequest
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
            'categories' => ['present', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
