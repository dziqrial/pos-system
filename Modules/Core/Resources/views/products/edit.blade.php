@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <div class="space-y-6">
        <!-- Edit Produk -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-800 pb-3 border-b border-gray-100 mb-4">Informasi Produk</h3>
            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Stok <span class="text-red-500">*</span></label>
                        <select name="stock_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="normal" {{ old('stock_type', $product->stock_type) == 'normal' ? 'selected' : '' }}>Normal (barcode)</option>
                            <option value="serial" {{ old('stock_type', $product->stock_type) == 'serial' ? 'selected' : '' }}>Serial (kode unik)</option>
                            <option value="bulk" {{ old('stock_type', $product->stock_type) == 'bulk' ? 'selected' : '' }}>Curah (tanpa barcode)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    </div>

                    <div class="flex items-center gap-2 pt-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 rounded">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Produk Aktif</label>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Perbarui Produk
                </button>
            </form>
        </div>

        <!-- Varian -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                <h3 class="text-base font-semibold text-gray-800">Varian Produk</h3>
                <button type="button" x-data @click="$dispatch('open-add-variant')"
                        class="text-sm text-indigo-600 font-medium hover:text-indigo-800">+ Tambah Varian</button>
            </div>

            <div class="space-y-3">
                @foreach($product->variants as $variant)
                <div x-data="{ editOpen: false }" class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ $variant->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">SKU: {{ $variant->sku }}</p>
                            <div class="flex gap-4 mt-1 text-sm text-gray-600">
                                <span>Harga: <strong>Rp {{ number_format($variant->price, 0, ',', '.') }}</strong></span>
                                <span>HPP: Rp {{ number_format($variant->cost, 0, ',', '.') }}</span>
                                <span>Satuan: {{ $variant->unit }} ({{ $variant->unit_type }})</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" @click="editOpen = !editOpen"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            @if($product->variants->count() > 1)
                            <form method="POST" action="{{ route('products.variants.destroy', [$product, $variant]) }}"
                                  onsubmit="return confirm('Hapus varian ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Edit form -->
                    <div x-show="editOpen" x-collapse class="mt-4 pt-4 border-t border-gray-100">
                        <form method="POST" action="{{ route('products.variants.update', [$product, $variant]) }}" class="grid grid-cols-2 gap-3">
                            @csrf @method('PUT')
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                                <input type="text" name="name" value="{{ $variant->name }}" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">SKU</label>
                                <input type="text" name="sku" value="{{ $variant->sku }}"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual</label>
                                <input type="number" name="price" value="{{ $variant->price }}" min="0" step="100" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">HPP</label>
                                <input type="number" name="cost" value="{{ $variant->cost }}" min="0" step="100"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                                <input type="text" name="unit" value="{{ $variant->unit }}" required
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Satuan</label>
                                <select name="unit_type" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    <option value="pcs" {{ $variant->unit_type == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    <option value="weight" {{ $variant->unit_type == 'weight' ? 'selected' : '' }}>Berat</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Barcode</label>
                                <input type="text" name="barcode" value="{{ $variant->barcode }}"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300">
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                        class="w-full px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Form Tambah Varian -->
            <div x-data="{ open: false }" x-on:open-add-variant.window="open = true" class="mt-4" x-show="open" x-collapse>
                <div class="border border-dashed border-indigo-300 rounded-lg p-4 bg-indigo-50">
                    <h4 class="text-sm font-semibold text-indigo-700 mb-3">Tambah Varian Baru</h4>
                    <form method="POST" action="{{ route('products.variants.store', $product) }}" class="grid grid-cols-2 gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Varian <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">SKU</label>
                            <input type="text" name="sku"
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                            <input type="number" name="price" min="0" step="100" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">HPP</label>
                            <input type="number" name="cost" min="0" step="100"
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Satuan <span class="text-red-500">*</span></label>
                            <input type="text" name="unit" value="pcs" required
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Satuan</label>
                            <select name="unit_type" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="pcs">Pcs</option>
                                <option value="weight">Berat</option>
                            </select>
                        </div>
                        <div class="col-span-2 flex gap-2">
                            <button type="submit"
                                    class="px-4 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">
                                Tambah Varian
                            </button>
                            <button type="button" @click="open = false"
                                    class="px-4 py-1.5 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
