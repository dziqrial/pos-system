<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('po_number')->unique()->comment('e.g. PO-20240101-0001');
            $table->enum('status', ['draft', 'sent', 'partial', 'received', 'cancelled'])
                  ->default('draft');
            $table->date('expected_at')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['outlet_id', 'status']);
        });

        Schema::create('po_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_rack_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Barang masuk diarahkan ke sub_rak ini');
            $table->decimal('qty_ordered', 15, 3);
            $table->decimal('qty_received', 15, 3)->default(0);
            $table->decimal('price', 15, 2)->comment('Harga beli dari supplier');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_items');
        Schema::dropIfExists('purchase_orders');
    }
};
