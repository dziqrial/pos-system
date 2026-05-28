@extends('layouts.app')

@section('title', 'Voucher')
@section('page-title', 'Daftar Voucher')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $vouchers->total() }} voucher</p>
    <a href="{{ route('vouchers.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Voucher
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kode / Nama</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Diskon</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Berlaku</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Terpakai</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($vouchers as $voucher)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-mono font-semibold text-gray-800">{{ $voucher->code }}</p>
                    <p class="text-xs text-gray-400">{{ $voucher->name }}</p>
                </td>
                <td class="px-4 py-3 font-medium text-indigo-600">
                    @if($voucher->type === 'percent')
                        {{ $voucher->value }}%
                        @if($voucher->max_discount)
                        <span class="text-xs text-gray-400">(maks Rp {{ number_format($voucher->max_discount, 0, ',', '.') }})</span>
                        @endif
                    @else
                        Rp {{ number_format($voucher->value, 0, ',', '.') }}
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    @if($voucher->started_at || $voucher->expired_at)
                        {{ $voucher->started_at?->format('d/m/y') ?? '∞' }} - {{ $voucher->expired_at?->format('d/m/y') ?? '∞' }}
                    @else
                        Tidak terbatas
                    @endif
                </td>
                <td class="px-4 py-3 text-right text-gray-600">
                    {{ $voucher->used_count }}{{ $voucher->quota ? ' / ' . $voucher->quota : '' }}
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $voucher->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $voucher->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('vouchers.edit', $voucher) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}"
                              onsubmit="return confirm('Hapus voucher ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada voucher.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($vouchers->hasPages())
<div class="mt-4">{{ $vouchers->links() }}</div>
@endif
@endsection
