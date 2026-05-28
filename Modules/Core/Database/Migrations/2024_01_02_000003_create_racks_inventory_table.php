<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. Rak A, Rak B');
            $table->text('description')->nullable();
            $table->string('location_note')->nullable()->comment('Lantai 1, sudut kiri, dll');
            $table->timestamps();
        });

        Schema::create('sub_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. Baris 1, Kolom A, Shelf 3');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_rack_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty', 15, 3)->default(0);
            $table->decimal('min_qty', 15, 3)->default(0)->comment('Low stock alert threshold');
            $table->timestamps();

            // Satu produk-varian bisa ada di beberapa sub_rak berbeda
            $table->unique(['product_variant_id', 'outlet_id', 'sub_rack_id'], 'inventory_unique');
            $table->index(['outlet_id', 'qty']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['in', 'out', 'transfer', 'adjust', 'return'])
                  ->comment('in=masuk, out=keluar, transfer=antar outlet, adjust=opname, return=retur');
            $table->decimal('qty', 15, 3)->comment('Positif=masuk, Negatif=keluar');
            $table->decimal('qty_before', 15, 3)->comment('Stok sebelum perubahan');
            $table->decimal('qty_after', 15, 3)->comment('Stok setelah perubahan');
            $table->string('ref_type')->nullable()->comment('transaction, purchase_order, manual');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['inventory_id', 'created_at']);
            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('sub_racks');
        Schema::dropIfExists('racks');
    }
};
