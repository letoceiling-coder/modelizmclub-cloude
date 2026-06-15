<?php

declare(strict_types=1);

use App\Domains\Moderation\Enums\ModerationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('moderatable'); // posts, ads, communities, comments
            $table->string('status')->default(ModerationStatus::Pending->value)->index();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('decision')->nullable(); // approved | rejected | needs_revision
            $table->text('reason')->nullable();
            $table->json('flags')->nullable(); // сработавшие авто-правила
            $table->timestamps();

            $table->index(['moderatable_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_items');
    }
};
