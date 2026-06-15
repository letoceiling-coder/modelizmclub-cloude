<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('bookmarkable'); // posts, ads
            $table->timestamps();

            $table->unique(['user_id', 'bookmarkable_id', 'bookmarkable_type'], 'bookmarks_user_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
