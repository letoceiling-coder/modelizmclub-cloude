<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('reactable'); // posts, comments
            $table->string('type')->default('like');
            $table->timestamps();

            // Один пользователь — одна реакция на сущность
            $table->unique(['user_id', 'reactable_id', 'reactable_type'], 'reactions_user_target_unique');
            $table->index(['reactable_id', 'reactable_type', 'type'], 'reactions_target_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
