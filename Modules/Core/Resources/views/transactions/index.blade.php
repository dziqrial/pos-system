@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Riwayat Transaksi')

@section('content')
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Cari kode transaksi..."
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-52">
    <input type="date" name="date_from" value="{{ request('date_from') }}"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    <input type="date" name="date_to" value="{{ request('date_to') }}"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <option value="">Semua Status</option>
        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Void</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Filter</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kode</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kasir</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Outlet</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Waktu</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($transactions as $trx)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $trx->code }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $trx->user->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $trx->outlet->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $trx->created_at->format('d/m/y H:i') }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-800">
                    Rp {{ number_format($trx->total, 0, ',', '.') }}
                </td>
                <td class="px-4 py-3">
                    @php
                        $statusColors = ['completed' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'voided' => 'bg-red-100 text-red-700'];
                        $statusLabels = ['completed' => 'Selesai', 'pending' => 'Pending', 'voided' => 'Void'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$trx->status] ?? '' }}">
                        {{ $statusLabels[$trx->status] ?? $trx->status }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('transactions.show', $trx) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                        @if($trx->status === 'completed')
                        <form method="POST" action="{{ route('transactions.void', $trx) }}"
                              onsubmit="return confirm('Void transaksi ini? Stok akan dikembalikan.')">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Void</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transactions->hasPages())
<div class="mt-4">{{ $transactions->links() }}</div>
@endif
@endsection
