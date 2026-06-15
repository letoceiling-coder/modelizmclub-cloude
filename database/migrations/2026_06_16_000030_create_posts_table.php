<?php

declare(strict_types=1);

use App\Domains\Feed\Enums\PostStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('community_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            // Репост: ссылка на оригинальный пост
            $table->foreignId('repost_of_id')->nullable()->constrained('posts')->nullOnDelete();

            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->default(PostStatus::Pending->value);

            $table->boolean('is_pinned')->default(false);
            $table->timestamp('pinned_at')->nullable();

            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->unsignedBigInteger('reposts_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);

            // Модерация
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Индексы под ленту и фильтры
            $table->index(['status', 'published_at']);
            $table->index(['community_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
