<?php

declare(strict_types=1);

namespace App\Domains\Feed\Http\Requests;

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
        $maxPhotos = (int) config('modelizm.posts.max_photos', 10);
        $maxImageKb = (int) config('modelizm.media.image_max_size_kb', 15360);
        $maxVideoKb = (int) config('modelizm.posts.max_video_size_mb', 200) * 1024;

        return [
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000', 'required_without_all:photos,video,repost_of_id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'community_id' => ['nullable', 'integer', 'exists:communities,id'],
            'community_section_id' => ['nullable', 'integer', 'exists:community_sections,id'],
            'repost_of_id' => ['nullable', 'integer', 'exists:posts,id'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],
            'photos' => ['nullable', 'array', "max:{$maxPhotos}"],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', "max:{$maxImageKb}"],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', "max:{$maxVideoKb}"],
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
