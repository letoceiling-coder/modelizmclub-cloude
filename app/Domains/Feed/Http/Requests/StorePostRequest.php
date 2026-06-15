<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Requests;

use App\Support\Media\UploadPolicy;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
        [$photosArray, $photoItem] = UploadPolicy::arrayRules('post_photo');

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000', 'required_without_all:photos,video,repost_of_id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'community_id' => ['nullable', 'integer', 'exists:communities,id'],
            'community_section_id' => ['nullable', 'integer', 'exists:community_sections,id'],
            'repost_of_id' => ['nullable', 'integer', 'exists:posts,id'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'photos' => $photosArray,
            'photos.*' => $photoItem,
            'video' => array_merge(['nullable'], UploadPolicy::fileRules('post_video')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required_without_all' => 'Пост должен содержать текст, медиа или быть репостом.',
            'photos.max' => 'Превышено максимальное число фотографий.',
        ];
    }
}
