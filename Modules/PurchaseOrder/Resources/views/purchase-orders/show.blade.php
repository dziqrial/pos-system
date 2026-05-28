@extends('layouts.app')

@section('title', $purchaseOrder->po_number)
@section('page-title', 'Detail PO: ' . $purchaseOrder->po_number)

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <div class="flex gap-2">
            @if($purchaseOrder->status === 'draft')
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
               class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Edit</a>
            <form method="POST" action="{{ route('purchase-orders.submit', $purchaseOrder) }}">
                @csrf
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Kirim ke Supplier</button>
            </form>
            <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}"
                  onsubmit="return confirm('Batalkan PO ini?')">
                @csrf
                <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200">Batalkan</button>
            </form>
            @elseif(in_array($purchaseOrder->status, ['sent', 'partial']))
            <a href="{{ route('purchase-orders.receive-form', $purchaseOrder) }}"
               class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">Terima Barang</a>
            <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}"
                  onsubmit="return confirm('Batalkan PO ini?')">
                @csrf
                <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200">Batalkan</button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-400">No. PO</p>
                <p class="font-mono font-semibold text-gray-800">{{ $purchaseOrder->po_number }}</p>
            </div>
            <div>
                @php $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'partial' => 'bg-yellow-100 text-yellow-700', 'received' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700']; @endphp
                <p class="text-gray-400">Status</p>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$purchaseOrder->status] ?? '' }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            </div>
            <div>
                <p class="text-gray-400">Supplier</p>
                <p class="font-medium text-gray-700">{{ $purchaseOrder->supplier->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Outlet Tujuan</p>
                <p class="font-medium text-gray-700">{{ $purchaseOrder->outlet->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Tanggal Dibuat</p>
                <p class="text-gray-700">{{ $purchaseOrder->created_at->format('d M Y') }}</p>
            </div>
            @if($purchaseOrder->expected_at)
            <div>
                <p class="text-gray-400">Estimasi Tiba</p>
                <p class="text-gray-700">{{ $purchaseOrder->expected_at->format('d M Y') }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Produk</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Pesan</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Diterima</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Harga Beli</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($purchaseOrder->items as $item)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $item->productVariant->product->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $item->productVariant->name ?? '' }}</p>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->qty_ordered, 3) }}</td>
                    <td class="px-4 py-3 text-right {{ $item->qty_received >= $item->qty_ordered ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ number_format($item->qty_received, 3) }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-right font-semibold text-gray-700">TOTAL</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-800">Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
