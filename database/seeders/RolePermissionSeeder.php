<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Users\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Контент
            'posts.moderate', 'posts.manage',
            'comments.moderate',
            'communities.manage', 'communities.moderate',
            'ads.moderate', 'ads.manage',
            // Справочники
            'categories.manage',
            // Пользователи и доступ
            'users.manage', 'roles.manage',
            // Монетизация
            'plans.manage', 'promocodes.manage', 'bonuses.manage', 'banners.manage',
            'payments.manage',
            // Жалобы и правила
            'reports.handle', 'content_rules.manage',
            // Система
            'settings.manage', 'analytics.view', 'audit.view',
            // Рассылки
            'campaigns.manage', 'support.handle',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = SpatieRole::findOrCreate(Role::User->value, 'web');
        $subscriber = SpatieRole::findOrCreate(Role::Subscriber->value, 'web');
        $moderator = SpatieRole::findOrCreate(Role::Moderator->value, 'web');
        $admin = SpatieRole::findOrCreate(Role::Admin->value, 'web');

        $moderator->syncPermissions([
            'posts.moderate', 'comments.moderate', 'communities.moderate',
            'ads.moderate', 'reports.handle', 'support.handle',
        ]);

        // Администратор получает все права
        $admin->syncPermissions(Permission::all());

        // Базовые роли без явных прав используют политики
        $user->syncPermissions([]);
        $subscriber->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
