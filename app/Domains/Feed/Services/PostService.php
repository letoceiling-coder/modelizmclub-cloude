<?php

declare(strict_types=1);

namespace App\Domains\Feed\Services;

use App\Domains\Catalog\Models\Tag;
use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Models\Post;
use App\Domains\Moderation\Enums\ModerationStatus;
use App\Domains\Moderation\Services\ContentModerationService;
use App\Domains\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(
        private readonly ContentModerationService $moderation,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function create(User $user, array $data, array $photos = [], ?UploadedFile $video = null): Post
    {
        $verdict = $this->moderation->check($data['title'] ?? null, $data['body'] ?? null);
        $status = $verdict['action'] === 'block' ? PostStatus::Pending : PostStatus::Published;

        return DB::transaction(function () use ($user, $data, $photos, $video, $verdict, $status): Post {
            $post = new Post([
                'community_id' => $data['community_id'] ?? null,
                'community_section_id' => $data['community_section_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'repost_of_id' => $data['repost_of_id'] ?? null,
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'status' => $status->value,
            ]);
            $post->user_id = $user->id;
            $post->save();

            $this->syncTags($post, $data['tags'] ?? [], $data['body'] ?? '');
            $this->attachMedia($post, $photos, $video);

            if ($verdict['action'] !== 'pass') {
                $post->moderation()->create([
                    'status' => ModerationStatus::Pending->value,
                    'submitted_by' => $user->id,
                    'submitted_at' => now(),
                    'flags' => $verdict['matches'],
                ]);
            }

            return $post;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function update(Post $post, array $data, array $photos = [], ?UploadedFile $video = null): Post
    {
        return DB::transaction(function () use ($post, $data, $photos, $video): Post {
            $post->fill(array_filter([
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'category_id' => $data['category_id'] ?? null,
            ], static fn ($v) => $v !== null));
            $post->save();

            if (array_key_exists('tags', $data)) {
                $this->syncTags($post, $data['tags'] ?? [], $data['body'] ?? $post->body ?? '');
            }

            $this->attachMedia($post, $photos, $video);

            return $post;
        });
    }

    /**
     * Парсинг хэштегов из текста + явные теги, обновление счётчиков использования.
     *
     * @param  array<int, string>  $explicit
     */
    private function syncTags(Post $post, array $explicit, string $body): void
    {
        preg_match_all('/#([\p{L}\p{N}_]{2,50})/u', $body, $found);
        $names = collect($explicit)
            ->merge($found[1] ?? [])
            ->map(static fn (string $name): string => Tag::normalize($name))
            ->filter()
            ->unique()
            ->take(20);

        $ids = $names->map(function (string $name): int {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($name) ?: $name],
                ['name' => $name],
            );
            $tag->increment('usage_count');

            return $tag->id;
        });

        $post->tags()->sync($ids->all());
    }

    /**
     * @param  array<int, UploadedFile>  $photos
     */
    private function attachMedia(Post $post, array $photos, ?UploadedFile $video): void
    {
        $limit = (int) config('modelizm.posts.max_photos', 10);

        foreach (array_slice($photos, 0, $limit) as $photo) {
            $post->addMedia($photo)->toMediaCollection('photos');
        }

        if ($video instanceof UploadedFile) {
            $post->addMedia($video)->toMediaCollection('video');
        }
    }
}
