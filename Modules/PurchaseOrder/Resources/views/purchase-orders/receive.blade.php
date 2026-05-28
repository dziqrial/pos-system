@extends('layouts.app')

@section('title', 'Terima Barang')
@section('page-title', 'Terima Barang: ' . $purchaseOrder->po_number)

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-4">Outlet: <strong>{{ $purchaseOrder->outlet->name }}</strong> · Supplier: <strong>{{ $purchaseOrder->supplier->name }}</strong></p>

        <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" class="space-y-3">
            @csrf

            @foreach($purchaseOrder->items as $item)
            <div class="flex items-center gap-4 p-3 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ $item->productVariant->product->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">Dipesan: {{ number_format($item->qty_ordered, 3) }} · Sudah diterima: {{ number_format($item->qty_received, 3) }}</p>
                </div>
                <div class="w-32">
                    <label class="block text-xs text-gray-500 mb-1">Qty Diterima Kali Ini</label>
                    <input type="number"
                           name="items[{{ $item->id }}][qty_received]"
                           value="{{ $item->qty_ordered - $item->qty_received }}"
                           min="0"
                           max="{{ $item->qty_ordered - $item->qty_received }}"
                           step="0.001"
                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                </div>
            </div>
            @endforeach

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                    Konfirmasi Penerimaan
                </button>
                <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
                   class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
