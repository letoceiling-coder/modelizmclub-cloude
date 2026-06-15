<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Планировщик
|--------------------------------------------------------------------------
| Запускается supervisor'ом (deploy/supervisor/modelizm-scheduler.conf)
| либо системным cron: * * * * * php artisan schedule:run
*/

// Резервное копирование БД (spatie/laravel-backup) — ежедневно ночью
Schedule::command('backup:clean')->dailyAt('02:30')->onOneServer();
Schedule::command('backup:run --only-db')->dailyAt('03:00')->onOneServer();

// Метрики Horizon (снимки нагрузки очередей)
Schedule::command('horizon:snapshot')->everyFiveMinutes();
