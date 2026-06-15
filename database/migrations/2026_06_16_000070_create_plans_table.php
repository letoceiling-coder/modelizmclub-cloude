<?php

declare(strict_types=1);

use App\Domains\Billing\Enums\PlanPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('period')->default(PlanPeriod::Month->value);
            $table->unsignedInteger('photo_limit')->default(10);
            $table->unsignedInteger('ad_priority')->default(0);
            $table->unsignedInteger('free_ads_count')->default(0);
            $table->unsignedInteger('discount_percent')->default(0);
            $table->string('badge')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
