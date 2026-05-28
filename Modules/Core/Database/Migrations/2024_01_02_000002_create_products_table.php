<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('barcode')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_variants')->default(false);

            /**
             * stock_type menentukan cara kerja stok & kasir:
             *
             * 'normal' — produk dengan barcode, stok dihitung qty (botol, kaleng, dll)
             *            Kasir scan barcode → input qty → stok berkurang sejumlah qty
             *
             * 'serial' — tiap unit punya kode unik (IMEI, lisensi, voucher fisik, dll)
             *            Stok = jumlah kode unik yang tersedia di tabel product_serial_numbers
             *            Kasir scan/input qty N → sistem hapus N kode random (status → sold)
             *            Kode yang terhapus tercatat di transaction_items.meta['serial_numbers']
             *
             * 'bulk'   — produk curah tanpa barcode (terigu, beras, dll yang dikemas sendiri)
             *            Kasir input qty manual (bisa desimal untuk satuan berat)
             *            Stok berkurang sesuai qty yang diinput
             */
            $table->enum('stock_type', ['normal', 'serial', 'bulk'])->default('normal');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'is_active']);
            $table->index(['store_id', 'stock_type']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('e.g. 250ml, L, Red. Default: same as product name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0)->comment('HPP / Cost of goods');

            /**
             * unit      — satuan tampilan (pcs, kg, gram, liter, meter, dll)
             * unit_type — cara input qty di kasir:
             *   'pcs'    → qty integer, stepper +1/-1, atau scan barcode ulang
             *   'weight' → qty desimal (3 angka di belakang koma), kasir input manual / timbangan
             */
            $table->string('unit', 20)->default('pcs')->comment('Label satuan: pcs, kg, gram, liter, meter');
            $table->enum('unit_type', ['pcs', 'weight'])->default('pcs');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Tabel kode unik per unit — hanya dipakai produk dengan stock_type = 'serial'
         *
         * Alur masuk stok:
         *   - Input manual satu per satu (misal IMEI HP diketik waktu terima barang dari PO)
         *   - Generate otomatis oleh sistem (format bisa dikonfigurasi per produk)
         *   - Bulk import via CSV
         *
         * Alur keluar stok (transaksi):
         *   - Kasir scan/input qty N
         *   - StockService::consumeSerials($variantId, $outletId, N) dipanggil
         *   - Ambil N kode dengan status 'available' secara RANDOM (inRandomOrder()->limit(N))
         *   - Status diubah ke 'sold', transaction_id diisi
         *   - Kode-kode ini disimpan di transaction_items.meta['serial_numbers'] untuk struk
         */
        Schema::create('product_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->string('serial_code')->comment('Kode unik per unit: IMEI, lisensi, nomor voucher, dll');
            $table->enum('status', ['available', 'sold', 'void'])->default('available');
            $table->enum('source', ['manual', 'generated', 'import'])->default('manual')
                  ->comment('Asal kode: diinput manual, di-generate sistem, atau import CSV');
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Diisi saat status berubah ke sold');
            $table->foreignId('po_item_id')->nullable()->comment('Referensi dari PO jika ada');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Kode unik per varian per outlet — tidak boleh duplikat yang available
            $table->unique(['product_variant_id', 'outlet_id', 'serial_code'], 'serial_unique');
            $table->index(['product_variant_id', 'outlet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serial_numbers');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
