<?php

declare(strict_types=1);

namespace App\Domains\Users\Http\Requests;

use App\Domains\Users\Enums\Gender;
use App\Domains\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'nullable', 'string', 'max:50', 'alpha_dash', Rule::unique(User::class, 'username')->ignore($userId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city_id' => ['sometimes', 'nullable', 'integer', 'exists:cities,id'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'birthdate' => ['sometimes', 'nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'nullable', Rule::in(Gender::values())],
        ];
    }
}
