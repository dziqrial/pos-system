@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Daftar Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $products->total() }} produk ditemukan</p>
    <a href="{{ route('products.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Produk
    </a>
</div>

<!-- Filter -->
<form method="GET" class="flex flex-wrap gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari nama / barcode..."
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-52">

    <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
    </select>

    <select name="stock_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <option value="">Semua Tipe Stok</option>
        <option value="normal" {{ request('stock_type') == 'normal' ? 'selected' : '' }}>Normal</option>
        <option value="serial" {{ request('stock_type') == 'serial' ? 'selected' : '' }}>Serial</option>
        <option value="bulk" {{ request('stock_type') == 'bulk' ? 'selected' : '' }}>Curah</option>
    </select>

    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Filter</button>
    @if(request()->anyFilled(['search','category_id','stock_type']))
    <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Produk</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kategori</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Tipe Stok</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($products as $product)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $product->name }}</p>
                    @if($product->barcode)
                    <p class="text-xs text-gray-400 font-mono">{{ $product->barcode }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $product->category?->name ?? '-' }}</td>
                <td class="px-4 py-3">
                    @php
                        $typeColors = ['normal' => 'bg-blue-100 text-blue-700', 'serial' => 'bg-purple-100 text-purple-700', 'bulk' => 'bg-orange-100 text-orange-700'];
                        $typeLabels = ['normal' => 'Normal', 'serial' => 'Serial', 'bulk' => 'Curah'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$product->stock_type] ?? '' }}">
                        {{ $typeLabels[$product->stock_type] ?? $product->stock_type }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('products.show', $product) }}" class="text-gray-500 hover:text-gray-700 font-medium">Detail</a>
                        <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                              onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada produk.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($products->hasPages())
<div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
