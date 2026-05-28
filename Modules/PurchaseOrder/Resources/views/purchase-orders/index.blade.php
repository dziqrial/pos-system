@extends('layouts.app')

@section('title', 'Purchase Order')
@section('page-title', 'Purchase Order')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex gap-2">
        @foreach(['', 'draft', 'sent', 'partial', 'received', 'cancelled'] as $s)
        <a href="{{ route('purchase-orders.index', $s ? ['status' => $s] : []) }}"
           class="px-3 py-1.5 text-xs font-medium rounded-lg {{ request('status') === $s || (!request('status') && $s === '') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ $s ? ucfirst($s) : 'Semua' }}
        </a>
        @endforeach
    </div>
    <a href="{{ route('purchase-orders.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Buat PO
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">No. PO</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Supplier</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Outlet</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Tanggal</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pos as $po)
            @php
                $statusColors = ['draft' => 'bg-gray-100 text-gray-600', 'sent' => 'bg-blue-100 text-blue-700', 'partial' => 'bg-yellow-100 text-yellow-700', 'received' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700'];
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $po->po_number }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $po->supplier->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $po->outlet->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $po->created_at->format('d/m/y') }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$po->status] ?? '' }}">
                        {{ ucfirst($po->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada purchase order.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pos->hasPages())
<div class="mt-4">{{ $pos->links() }}</div>
@endif
@endsection
