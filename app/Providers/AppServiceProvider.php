<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Communities\Models\Community;
use App\Domains\Communities\Models\CommunityMember;
use App\Domains\Communities\Observers\CommunityMemberObserver;
use App\Domains\Communities\Policies\CommunityPolicy;
use App\Domains\Feed\Models\Comment;
use App\Domains\Feed\Models\Post;
use App\Domains\Feed\Models\Reaction;
use App\Domains\Feed\Observers\CommentObserver;
use App\Domains\Feed\Observers\PostObserver;
use App\Domains\Feed\Observers\ReactionObserver;
use App\Domains\Feed\Policies\CommentPolicy;
use App\Domains\Feed\Policies\PostPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Фабрики доменных моделей живут в database/factories с именем <Модель>Factory
        Factory::guessFactoryNamesUsing(
            static fn (string $modelName): string => 'Database\\Factories\\'.Str::afterLast($modelName, '\\').'Factory'
        );

        // Наблюдатели счётчиков и древовидных веток
        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);
        Reaction::observe(ReactionObserver::class);
        CommunityMember::observe(CommunityMemberObserver::class);

        // Политики доступа доменных моделей
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Community::class, CommunityPolicy::class);

        // Доступ к OpenAPI-документации (Scramble): локально всегда,
        // на дев-сервере — через флаг SCRAMBLE_ENABLED.
        Gate::define('viewApiDocs', static function ($user = null): bool {
            return ! app()->isProduction() || (bool) config('scramble.docs_enabled');
        });

        // Стабильные алиасы полиморфных связей для API/БД
        Relation::morphMap([
            'user' => \App\Domains\Users\Models\User::class,
            'post' => Post::class,
            'comment' => Comment::class,
            'community' => Community::class,
            'ad' => \App\Domains\Ads\Models\Ad::class,
            'message' => \App\Domains\Messaging\Models\Message::class,
        ]);

        // Строгий режим Eloquent в разработке: ловим N+1 и обращения к незагруженным атрибутам
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);

        // Регистрация социальных провайдеров (VK ID, Яндекс ID)
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('vkontakte', \SocialiteProviders\VKontakte\Provider::class);
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
        });
    }
}
