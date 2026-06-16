<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\City;
use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Enums\CommunityStatus;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunitySection;
use App\Domains\Feed\Enums\CommentStatus;
use App\Domains\Feed\Enums\PostStatus;
use App\Domains\Feed\Enums\ReactionType;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Feed\Models\Reaction;
use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Детерминированные QA-данные без Faker/factories (работает на prod без dev-зависимостей).
 */
class QaContentSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::query()->first();
        $contentCategory = Category::ofType(CategoryType::Content->value)->first();
        $communityCategory = Category::ofType(CategoryType::Community->value)->first();

        $users = collect([
            ['name' => 'QA User 1', 'username' => 'qa_user_1', 'email' => 'qa_user_1@modelizmclub.ru'],
            ['name' => 'QA User 2', 'username' => 'qa_user_2', 'email' => 'qa_user_2@modelizmclub.ru'],
            ['name' => 'QA User 3', 'username' => 'qa_user_3', 'email' => 'qa_user_3@modelizmclub.ru'],
            ['name' => 'QA User 4', 'username' => 'qa_user_4', 'email' => 'qa_user_4@modelizmclub.ru'],
            ['name' => 'QA User 5', 'username' => 'qa_user_5', 'email' => 'qa_user_5@modelizmclub.ru'],
        ])->map(function (array $attrs) use ($city): User {
            $user = User::query()->updateOrCreate(
                ['email' => $attrs['email']],
                [
                    'name' => $attrs['name'],
                    'username' => $attrs['username'],
                    'password' => Hash::make('password'),
                    'status' => UserStatus::Active->value,
                    'email_verified_at' => now(),
                    'city_id' => $city?->id,
                ],
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            BonusAccount::query()->firstOrCreate(['user_id' => $user->id], ['balance' => 100]);

            return $user;
        });

        $demoUser = User::query()->where('email', config('sanctum.email'))->first();
        if ($demoUser) {
            $users->prepend($demoUser);
        }

        $communities = collect([
            ['name' => 'QA Сообщество «Модели»', 'slug' => 'qa-models'],
            ['name' => 'QA Сообщество «Краски»', 'slug' => 'qa-paints'],
        ])->map(function (array $attrs, int $index) use ($users, $communityCategory): Community {
            $owner = $users[$index % $users->count()];

            $community = Community::query()->updateOrCreate(
                ['slug' => $attrs['slug']],
                [
                    'owner_id' => $owner->id,
                    'category_id' => $communityCategory?->id,
                    'name' => $attrs['name'],
                    'description' => 'Тестовое сообщество для Swagger CRUD.',
                    'status' => CommunityStatus::Active->value,
                ],
            );

            CommunitySection::query()->updateOrCreate(
                ['community_id' => $community->id, 'name' => 'Обсуждения'],
                ['position' => 0],
            );
            CommunitySection::query()->updateOrCreate(
                ['community_id' => $community->id, 'name' => 'Работы'],
                ['position' => 1],
            );

            foreach ($users->take(4) as $i => $member) {
                $community->members()->updateOrCreate(
                    ['user_id' => $member->id],
                    [
                        'role' => $i === 0
                            ? CommunityMemberRole::Moderator->value
                            : CommunityMemberRole::Member->value,
                        'joined_at' => now(),
                    ],
                );
            }

            return $community;
        });

        $posts = collect([
            ['title' => 'QA пост: сборка танка', 'body' => 'Тестовый пост для редактирования и удаления в Swagger.'],
            ['title' => 'QA пост: аэрограф', 'body' => 'Второй тестовый пост с комментариями.'],
            ['title' => 'QA пост: diorama', 'body' => 'Третий пост в сообществе.'],
        ])->map(function (array $attrs, int $index) use ($users, $contentCategory, $communities): Post {
            $author = $users[$index % $users->count()];
            $community = $index === 2 ? $communities->first() : null;

            return Post::query()->updateOrCreate(
                ['user_id' => $author->id, 'title' => $attrs['title']],
                [
                    'body' => $attrs['body'],
                    'category_id' => $contentCategory?->id,
                    'community_id' => $community?->id,
                    'status' => PostStatus::Published->value,
                    'published_at' => now()->subDays($index),
                ],
            );
        });

        foreach ($posts as $postIndex => $post) {
            $commenter = $users[($postIndex + 1) % $users->count()];

            Comment::query()->updateOrCreate(
                ['post_id' => $post->id, 'user_id' => $commenter->id, 'body' => 'QA-комментарий к посту #'.($postIndex + 1)],
                ['status' => CommentStatus::Published->value],
            );

            Reaction::query()->firstOrCreate(
                [
                    'reactable_type' => 'post',
                    'reactable_id' => $post->id,
                    'user_id' => $users->first()->id,
                ],
                ['type' => ReactionType::Like->value],
            );
        }
    }
}
