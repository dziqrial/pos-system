@extends('layouts.app')

@section('title', $customer->name)
@section('page-title', $customer->name)

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h2>
                @if($customer->phone)<p class="text-sm text-gray-500">📞 {{ $customer->phone }}</p>@endif
                @if($customer->email)<p class="text-sm text-gray-500">✉️ {{ $customer->email }}</p>@endif
            </div>
            <a href="{{ route('customers.edit', $customer) }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Edit
            </a>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-indigo-50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($customer->points_balance) }}</p>
                <p class="text-xs text-indigo-500 mt-1">Saldo Poin</p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600 capitalize">{{ $customer->tier }}</p>
                <p class="text-xs text-yellow-500 mt-1">Tier</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-lg font-bold text-green-600">Rp {{ number_format($customer->total_spend, 0, ',', '.') }}</p>
                <p class="text-xs text-green-500 mt-1">Total Belanja</p>
            </div>
        </div>
    </div>

    <!-- Riwayat Poin -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Riwayat Poin</h3>
        @if($customer->loyaltyPoints->count())
        <div class="space-y-2">
            @foreach($customer->loyaltyPoints as $point)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div>
                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $point->type === 'earn' ? 'bg-green-100 text-green-700' : ($point->type === 'redeem' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($point->type) }}
                    </span>
                    @if($point->note)
                    <span class="ml-2 text-xs text-gray-400">{{ $point->note }}</span>
                    @endif
                </div>
                <div class="text-right">
                    <p class="font-semibold {{ $point->delta >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $point->delta >= 0 ? '+' : '' }}{{ number_format($point->delta) }}
                    </p>
                    <p class="text-xs text-gray-400">Saldo: {{ number_format($point->balance) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400 text-center py-4">Belum ada riwayat poin.</p>
        @endif
    </div>
</div>
@endsection
