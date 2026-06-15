<?php

declare(strict_types=1);

namespace App\Domains\Communities\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityApplicationRequest extends FormRequest
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
            'proposed_name' => ['required', 'string', 'min:3', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proposed_name.required' => 'Укажите название сообщества.',
            'category_id.required' => 'Выберите категорию сообщества.',
            'description.required' => 'Опишите тематику сообщества.',
        ];
    }
}
