<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

it('registers qa:reset command', function (): void {
    expect(Artisan::all())->toHaveKey('qa:reset');
});

it('defines qa sandbox database connection', function (): void {
    expect(config('modelizm.qa.connection'))->toBe('pgsql_qa')
        ->and(config('database.connections.pgsql_qa'))->toBeArray()
        ->and(config('database.connections.pgsql_qa.database'))->toBeString();
});

it('QaDatabaseSeeder includes demo content seeder', function (): void {
    $source = file_get_contents(database_path('seeders/QaDatabaseSeeder.php'));

    expect($source)->toContain('QaContentSeeder::class')
        ->and($source)->toContain('DatabaseSeeder::class');
});
