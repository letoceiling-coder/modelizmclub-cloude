<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Полный набор данных для QA / Swagger Try It: справочники, demo/admin,
 * пользователи, сообщества, посты, комментарии, реакции.
 */
class QaDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
