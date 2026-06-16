<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\QaDatabaseSeeder;
use Illuminate\Console\Command;

class QaResetCommand extends Command
{
    protected $signature = 'qa:reset
                            {--force : Выполнить без подтверждения (для CI/деплоя)}';

    protected $description = 'Пересоздать QA-базу: migrate:fresh + QaDatabaseSeeder (справочники + demo-контент)';

    public function handle(): int
    {
        $connection = (string) config('modelizm.qa.connection', 'pgsql_qa');

        if (! config('database.connections.'.$connection)) {
            $this->error("Подключение [{$connection}] не найдено в config/database.php");

            return self::FAILURE;
        }

        if (app()->isProduction() && ! $this->option('force') && ! $this->confirm('Окружение production. Пересоздать QA-базу?')) {
            return self::FAILURE;
        }

        $this->info("→ migrate:fresh --seed на подключении [{$connection}]");

        $exit = $this->call('migrate:fresh', [
            '--database' => $connection,
            '--force' => true,
            '--seed' => true,
            '--seeder' => QaDatabaseSeeder::class,
        ]);

        if ($exit !== self::SUCCESS) {
            return $exit;
        }

        $dbName = config("database.connections.{$connection}.database");
        $this->info("✓ QA-база [{$dbName}] готова к тестам Swagger");

        return self::SUCCESS;
    }
}
