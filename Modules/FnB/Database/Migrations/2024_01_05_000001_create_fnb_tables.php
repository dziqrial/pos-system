<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. Meja 1, VIP 2');
            $table->integer('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });

        Schema::create('kitchen_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'cooking', 'ready', 'served'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamp('cooked_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamps();

            $table->index(['table_id', 'status']);
        });

        Schema::create('kitchen_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_item_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'cooking', 'done'])->default('pending');
            $table->text('note')->nullable()->comment('e.g. less spicy, no onion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_items');
        Schema::dropIfExists('kitchen_orders');
        Schema::dropIfExists('tables');
    }
};
