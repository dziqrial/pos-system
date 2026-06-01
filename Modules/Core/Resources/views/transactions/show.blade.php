@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        @if($transaction->status === 'completed')
        <form method="POST" action="{{ route('transactions.void', $transaction) }}"
              onsubmit="return confirm('Void transaksi ini? Stok akan dikembalikan.')">
            @csrf
            <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                Void Transaksi
            </button>
        </form>
        @endif
    </div>

    <!-- Header struk -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800 font-mono">{{ $transaction->code }}</h2>
                <p class="text-sm text-gray-500">{{ $transaction->created_at->format('d M Y, H:i:s') }}</p>
            </div>
            @php
                $statusColors = ['completed' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'voided' => 'bg-red-100 text-red-700'];
                $statusLabels = ['completed' => 'Selesai', 'pending' => 'Pending', 'voided' => 'VOID'];
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$transaction->status] ?? '' }}">
                {{ $statusLabels[$transaction->status] ?? $transaction->status }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 text-sm border-t border-gray-100 pt-4">
            <div>
                <p class="text-gray-400">Kasir</p>
                <p class="font-medium text-gray-700">{{ $transaction->user->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Outlet</p>
                <p class="font-medium text-gray-700">{{ $transaction->outlet->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Shift</p>
                <p class="font-medium text-gray-700">#{{ $transaction->shift_id }}</p>
            </div>
            @if($transaction->customer_id)
            <div>
                <p class="text-gray-400">Customer ID</p>
                <p class="font-medium text-gray-700">{{ $transaction->customer_id }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Items -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Item Transaksi</h3>
        <div class="space-y-3">
            @foreach($transaction->items as $item)
            <div class="flex items-start justify-between py-2 border-b border-gray-100 last:border-0">
                <div>
                    <p class="font-medium text-gray-800">{{ $item->name }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($item->qty, 0) }} × Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                    @if(isset($item->meta['serial_numbers']) && count($item->meta['serial_numbers']))
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($item->meta['serial_numbers'] as $sn)
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded text-xs font-mono text-gray-600">{{ $sn }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="text-right">
                    @if($item->discount_amount > 0)
                    <p class="text-xs text-gray-400 line-through">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</p>
                    @endif
                    <p class="font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount_amount > 0)
            <div class="flex justify-between text-red-600">
                <span>Diskon</span>
                <span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($transaction->tax_amount > 0)
            <div class="flex justify-between text-gray-600">
                <span>Pajak</span>
                <span>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-gray-800 text-base border-t border-gray-200 pt-2 mt-2">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Pembayaran -->
    @if($transaction->payments->count())
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Pembayaran</h3>
        <div class="space-y-2 text-sm">
            @foreach($transaction->payments as $payment)
            <div class="flex justify-between">
                <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $payment->method) }}</span>
                <span class="font-medium text-gray-800">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
