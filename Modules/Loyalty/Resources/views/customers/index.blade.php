@extends('layouts.app')

@section('title', 'Customer')
@section('page-title', 'Daftar Customer')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $customers->total() }} customer terdaftar</p>
    <a href="{{ route('customers.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Customer
    </a>
</div>

<form method="GET" class="mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari nama / telepon / email..."
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-64">
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Cari</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Nama</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Telepon</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Tier</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Poin</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Total Belanja</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($customers as $customer)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                    @if($customer->email)
                    <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $customer->phone ?? '-' }}</td>
                <td class="px-4 py-3">
                    @php
                        $tierColors = ['bronze' => 'bg-orange-100 text-orange-700', 'silver' => 'bg-gray-100 text-gray-700', 'gold' => 'bg-yellow-100 text-yellow-700', 'platinum' => 'bg-purple-100 text-purple-700'];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $tierColors[$customer->tier] ?? '' }}">
                        {{ ucfirst($customer->tier) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-indigo-600">{{ number_format($customer->points_balance) }}</td>
                <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($customer->total_spend, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('customers.show', $customer) }}" class="text-gray-500 hover:text-gray-700 font-medium">Detail</a>
                        <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                              onsubmit="return confirm('Hapus customer ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada customer.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($customers->hasPages())
<div class="mt-4">{{ $customers->links() }}</div>
@endif
@endsection
