@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<!-- Filter periode -->
<form method="GET" class="flex flex-wrap gap-3 mb-6">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
        <input type="date" name="date_from" value="{{ request('date_from', $dateFrom) }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
        <input type="date" name="date_to" value="{{ request('date_to', $dateTo) }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Outlet</label>
        <select name="outlet_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Semua Outlet</option>
            @foreach($outlets ?? [] as $outlet)
            <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-end">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
            Tampilkan
        </button>
    </div>
</form>

<!-- Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Penjualan</p>
        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $summary['transaction_count'] ?? 0 }} transaksi</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Rata-rata Transaksi</p>
        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($summary['avg_transaction'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Diskon</p>
        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($summary['total_discount'] ?? 0, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Transaksi Void</p>
        <p class="text-2xl font-bold text-gray-500">{{ $summary['void_count'] ?? 0 }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Penjualan per Hari -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Penjualan Harian</h3>
        @if(!empty($dailySales))
        <div class="space-y-2">
            @foreach($dailySales as $day)
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 w-20 flex-shrink-0">{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    @php $maxRev = collect($dailySales)->max('revenue'); @endphp
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $maxRev > 0 ? ($day->revenue / $maxRev * 100) : 0 }}%"></div>
                </div>
                <span class="text-xs font-medium text-gray-700 w-28 text-right">Rp {{ number_format($day->revenue, 0, ',', '.') }}</span>
                <span class="text-xs text-gray-400 w-12 text-right">{{ $day->count }} txn</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Tidak ada data untuk periode ini.</p>
        @endif
    </div>

    <!-- Top Produk -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Produk Terlaris</h3>
        @if(!empty($topProducts))
        <div class="space-y-3">
            @foreach($topProducts as $i => $item)
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold flex items-center justify-center flex-shrink-0">
                    {{ $i + 1 }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $item->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">{{ number_format($item->total_qty, 0) }} unit</p>
                    <p class="text-xs text-gray-400">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Tidak ada data untuk periode ini.</p>
        @endif
    </div>

    <!-- Metode Pembayaran -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Metode Pembayaran</h3>
        @if(!empty($paymentMethods))
        <div class="space-y-2">
            @php $totalPayment = collect($paymentMethods)->sum('amount'); @endphp
            @foreach($paymentMethods as $method)
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600 w-16 capitalize">{{ str_replace('_', ' ', $method->method) }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $totalPayment > 0 ? ($method->amount / $totalPayment * 100) : 0 }}%"></div>
                </div>
                <span class="text-sm font-medium text-gray-700 w-32 text-right">Rp {{ number_format($method->amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Tidak ada data untuk periode ini.</p>
        @endif
    </div>

    <!-- Stok Minim -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">⚠️ Stok Hampir Habis</h3>
        @if(!empty($lowStockItems) && $lowStockItems->count())
        <div class="space-y-2">
            @foreach($lowStockItems as $inv)
            <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $inv->productVariant?->product?->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">{{ $inv->outlet?->name ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-red-600">{{ number_format($inv->qty, 0) }}</p>
                    <p class="text-xs text-gray-400">min: {{ number_format($inv->min_qty, 0) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-green-600 text-center py-4">✅ Semua stok dalam kondisi aman.</p>
        @endif
    </div>
</div>
@endsection
