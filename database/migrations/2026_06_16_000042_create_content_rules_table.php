<?php

declare(strict_types=1);

use App\Domains\Moderation\Enums\ContentRuleAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // stopword | banned_link
            $table->string('value');
            $table->string('action')->default(ContentRuleAction::Flag->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_rules');
    }
};
