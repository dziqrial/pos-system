<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 20)->comment('e.g. 1-001');
            $table->string('name')->comment('Kas, Piutang Dagang, Pendapatan Penjualan, dll');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'code']);
            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->comment('e.g. JRN-20240101-0001');
            $table->string('description');
            $table->date('date');
            $table->string('ref_type')->nullable()->comment('transaction, purchase_order, manual');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'date']);
            $table->index(['ref_type', 'ref_id']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
