<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique()->comment('e.g. loyalty, purchase_order, fnb');
            $table->string('version')->default('1.0.0');
            $table->boolean('is_core')->default(false);
            $table->timestamps();
        });

        Schema::create('store_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->json('config')->nullable()->comment('Per-module config per store');
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_modules');
        Schema::dropIfExists('modules');
    }
};
