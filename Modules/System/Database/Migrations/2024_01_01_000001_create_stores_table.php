<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['retail', 'fnb', 'pharmacy'])->default('retail');
            $table->json('settings')->nullable()->comment('General store config: currency, tax, logo, etc.');
            $table->boolean('is_active')->default(true);
            $table->string('timezone')->default('Asia/Jakarta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
