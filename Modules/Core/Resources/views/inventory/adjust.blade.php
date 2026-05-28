@extends('layouts.app')

@section('title', 'Penyesuaian Stok')
@section('page-title', 'Penyesuaian Stok')

@section('content')
<div class="max-w-lg">
    <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <!-- Info produk -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <p class="font-semibold text-gray-800">{{ $inventory->productVariant->product->name ?? '-' }}</p>
            <p class="text-sm text-gray-500">Varian: {{ $inventory->productVariant->name ?? '-' }}</p>
            <p class="text-sm text-gray-500">Outlet: {{ $inventory->outlet->name ?? '-' }}</p>
            <div class="mt-2 flex gap-4 text-sm">
                <span>Stok saat ini: <strong class="text-gray-800">{{ number_format($inventory->qty, 3) }} {{ $inventory->productVariant?->unit }}</strong></span>
                <span>Min stok: <strong class="text-gray-800">{{ number_format($inventory->min_qty, 0) }}</strong></span>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.update', $inventory) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Penyesuaian <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="adjust">Opname (set stok ke nilai baru)</option>
                    <option value="in">Tambah stok (masuk)</option>
                    <option value="out">Kurangi stok (keluar)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                <input type="number" name="qty" step="{{ $inventory->productVariant?->unit_type === 'weight' ? '0.001' : '1' }}" min="0" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Min Stok (alert)</label>
                <input type="number" name="min_qty" value="{{ old('min_qty', $inventory->min_qty) }}" min="0" step="1"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="note" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                    Simpan Penyesuaian
                </button>
                <a href="{{ route('inventory.index') }}"
                   class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
