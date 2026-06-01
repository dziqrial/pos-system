@extends('layouts.app')

@section('title', 'Inventori')
@section('page-title', 'Manajemen Inventori')

@section('content')
@php
    $outletId = session('current_outlet_id');
@endphp

<div class="flex flex-wrap gap-3 mb-6">
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari produk..."
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-52">
        <select name="outlet_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Semua Outlet</option>
            @foreach($outlets ?? [] as $outlet)
            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Filter</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Produk / Varian</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Outlet</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Sub-Rak</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Stok</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Min Stok</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($inventories as $inv)
            <tr class="hover:bg-gray-50 {{ $inv->qty <= $inv->min_qty && $inv->min_qty > 0 ? 'bg-red-50' : '' }}">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $inv->productVariant->product->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">{{ $inv->productVariant->name }} · {{ $inv->productVariant->sku ?? '' }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $inv->outlet->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $inv->subRack?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-right">
                    <span class="font-semibold {{ $inv->qty <= $inv->min_qty && $inv->min_qty > 0 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ number_format($inv->qty, $inv->productVariant?->unit_type === 'weight' ? 3 : 0) }}
                    </span>
                    <span class="text-gray-400 text-xs">{{ $inv->productVariant?->unit }}</span>
                </td>
                <td class="px-4 py-3 text-right text-gray-400">{{ number_format($inv->min_qty, 0) }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('inventory.adjust', $inv) }}"
                       class="text-indigo-600 hover:text-indigo-800 font-medium">Sesuaikan</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada data inventori.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(isset($inventories) && $inventories->hasPages())
<div class="mt-4">{{ $inventories->links() }}</div>
@endif
@endsection
