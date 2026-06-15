<?php

declare(strict_types=1);

use App\Domains\Users\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('phone');
            // Город храним как индексируемую ссылку без жёсткого FK ради переносимости (PG/SQLite)
            $table->unsignedBigInteger('city_id')->nullable()->after('avatar_path');
            $table->text('bio')->nullable()->after('city_id');
            $table->date('birthdate')->nullable()->after('bio');
            $table->string('gender')->nullable()->after('birthdate');
            $table->unsignedInteger('rating')->default(0)->after('gender');
            $table->json('settings')->nullable()->after('rating');
            $table->json('privacy')->nullable()->after('settings');
            $table->string('status')->default(UserStatus::Active->value)->after('privacy');
            $table->timestamp('last_seen_at')->nullable()->after('status');
            $table->softDeletes();

            $table->index('city_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'username', 'phone', 'avatar_path', 'city_id', 'bio', 'birthdate',
                'gender', 'rating', 'settings', 'privacy', 'status', 'last_seen_at',
            ]);
        });
    }
};
