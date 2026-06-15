<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Requests;

use App\Support\Media\UploadPolicy;
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
        [, $photoItem] = UploadPolicy::arrayRules('post_photo');
        $maxPhotos = UploadPolicy::maxCount('post_photo');

        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'tags' => ['sometimes', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'photos' => ['sometimes', 'array', "max:{$maxPhotos}"],
            'photos.*' => $photoItem,
        ];
    }
}
