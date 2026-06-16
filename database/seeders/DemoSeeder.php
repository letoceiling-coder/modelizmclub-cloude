<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Catalog\Enums\CategoryType;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\City;
use App\Domains\Communities\Enums\CommunityMemberRole;
use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunitySection;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Feed\Models\Reaction;
use App\Domains\Promotions\Models\BonusAccount;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Демо-данные для фронтенда: пользователи, сообщества, посты, комментарии, реакции.
 * Запускать поверх базовых сидеров (категории/города уже должны существовать).
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $contentCategories = Category::ofType(CategoryType::Content->value)->get();
        $communityCategories = Category::ofType(CategoryType::Community->value)->get();
        $cities = City::all();

        $users = User::factory()->count(15)->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->each(function (User $user) use ($cities): void {
            $user->assignRole('user');
            $user->forceFill(['city_id' => $cities->random()->id ?? null])->save();
            BonusAccount::query()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
        });

        $communities = Community::factory()->count(5)->create([
            'category_id' => $communityCategories->random()->id ?? null,
        ]);

        foreach ($communities as $community) {
            $community->forceFill(['owner_id' => $users->random()->id])->save();

            CommunitySection::factory()->count(2)->create(['community_id' => $community->id]);

            $members = $users->random(min(6, $users->count()));
            foreach ($members as $i => $member) {
                $community->members()->updateOrCreate(
                    ['user_id' => $member->id],
                    [
                        'role' => $i === 0 ? CommunityMemberRole::Moderator->value : CommunityMemberRole::Member->value,
                        'joined_at' => now(),
                    ],
                );
            }
        }

        $posts = collect();
        foreach ($users as $user) {
            $count = random_int(1, 4);
            for ($i = 0; $i < $count; $i++) {
                $posts->push(Post::factory()->create([
                    'user_id' => $user->id,
                    'category_id' => $contentCategories->random()->id ?? null,
                    'community_id' => \fake()->boolean(40) ? $communities->random()->id : null,
                ]));
            }
        }

        foreach ($posts as $post) {
            $commenters = $users->random(random_int(0, 4));
            foreach ($commenters as $commenter) {
                $root = Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $commenter->id,
                ]);

                if (\fake()->boolean(40)) {
                    Comment::factory()->create([
                        'post_id' => $post->id,
                        'user_id' => $users->random()->id,
                        'parent_id' => $root->id,
                    ]);
                }
            }

            foreach ($users->random(random_int(0, 8)) as $liker) {
                Reaction::query()->firstOrCreate([
                    'reactable_type' => 'post',
                    'reactable_id' => $post->id,
                    'user_id' => $liker->id,
                ], ['type' => 'like']);
            }
        }
    }
}
