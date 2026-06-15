<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
        $maxPhotos = (int) config('modelizm.posts.max_photos', 10);
        $maxImageKb = (int) config('modelizm.media.image_max_size_kb', 15360);

        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'photos' => ['sometimes', 'array', "max:{$maxPhotos}"],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', "max:{$maxImageKb}"],
        ];
    }
}
