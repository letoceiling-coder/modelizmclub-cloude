<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_delivery_methods', function (Blueprint $table) {
            $table->foreignId('ad_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_method_id')->constrained()->cascadeOnDelete();
            $table->primary(['ad_id', 'delivery_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_delivery_methods');
    }
};
