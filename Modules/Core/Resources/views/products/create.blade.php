@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
        @csrf

        <!-- Informasi Produk -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="text-base font-semibold text-gray-800 pb-2 border-b border-gray-100">Informasi Produk</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Pilih Kategori —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Stok <span class="text-red-500">*</span></label>
                    <select name="stock_type" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="normal" {{ old('stock_type','normal') == 'normal' ? 'selected' : '' }}>Normal (barcode)</option>
                        <option value="serial" {{ old('stock_type') == 'serial' ? 'selected' : '' }}>Serial (kode unik per unit)</option>
                        <option value="bulk" {{ old('stock_type') == 'bulk' ? 'selected' : '' }}>Curah (tanpa barcode)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode Produk</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Varian Default -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
            <h3 class="text-base font-semibold text-gray-800 pb-2 border-b border-gray-100">Varian Default</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Varian <span class="text-red-500">*</span></label>
                    <input type="text" name="variant_name" value="{{ old('variant_name', 'Default') }}" required
                           placeholder="Default / 250ml / L"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" name="variant_sku" value="{{ old('variant_sku') }}"
                           placeholder="Otomatis jika kosong"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">Rp</span>
                        <input type="number" name="variant_price" value="{{ old('variant_price') }}" required min="0" step="100"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HPP / Modal</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-sm text-gray-400">Rp</span>
                        <input type="number" name="variant_cost" value="{{ old('variant_cost') }}" min="0" step="100"
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="variant_unit" value="{{ old('variant_unit', 'pcs') }}" required
                           placeholder="pcs, kg, liter, dll"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Satuan <span class="text-red-500">*</span></label>
                    <select name="variant_unit_type" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="pcs" {{ old('variant_unit_type','pcs') == 'pcs' ? 'selected' : '' }}>Pcs (qty integer)</option>
                        <option value="weight" {{ old('variant_unit_type') == 'weight' ? 'selected' : '' }}>Berat (qty desimal)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode Varian</label>
                    <input type="text" name="variant_barcode" value="{{ old('variant_barcode') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                Simpan Produk
            </button>
            <a href="{{ route('products.index') }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
