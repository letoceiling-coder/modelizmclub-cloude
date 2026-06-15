<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // subscription_discount | free_period | free_ads | discount
            $table->decimal('value', 12, 2)->default(0); // % или сумма в зависимости от типа
            $table->unsignedInteger('free_ads_count')->default(0);
            $table->unsignedInteger('duration_days')->default(0);
            $table->unsignedInteger('max_activations')->nullable();
            $table->unsignedInteger('max_per_user')->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // привязка к пользователю
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'ends_at']);
        });

        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->timestamp('used_at');
            $table->timestamps();

            $table->index(['promo_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
        Schema::dropIfExists('promo_codes');
    }
};
