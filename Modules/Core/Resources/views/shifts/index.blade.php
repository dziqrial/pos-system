@extends('layouts.app')

@section('title', 'Shift')
@section('page-title', 'Manajemen Shift')

@section('content')
@php $openShift = $shifts->firstWhere('status', 'open'); @endphp

<!-- Status Shift Aktif -->
@if($openShift)
<div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-semibold text-green-700">Shift Sedang Berlangsung</span>
            </div>
            <p class="text-sm text-green-600">Dibuka: {{ $openShift->opened_at->format('d M Y, H:i') }}</p>
            <p class="text-sm text-green-600">Kasir: {{ $openShift->user->name ?? '-' }} · Outlet: {{ $openShift->outlet->name ?? '-' }}</p>
            <p class="text-sm text-green-600">Modal Kas: Rp {{ number_format($openShift->cash_start, 0, ',', '.') }}</p>
        </div>
        <form method="POST" action="{{ route('shifts.close', $openShift) }}" class="flex flex-col items-end gap-2">
            @csrf
            <div>
                <label class="text-xs text-gray-600">Kas Akhir (Rp)</label>
                <input type="number" name="cash_end" min="0" step="1000" placeholder="0"
                       class="block border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-red-300">
            </div>
            <div>
                <label class="text-xs text-gray-600">Catatan</label>
                <input type="text" name="note"
                       class="block border border-gray-300 rounded px-2 py-1 text-sm w-40 focus:outline-none focus:ring-1 focus:ring-red-300">
            </div>
            <button type="submit"
                    onclick="return confirm('Tutup shift sekarang?')"
                    class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                Tutup Shift
            </button>
        </form>
    </div>
</div>
@else
<!-- Buka Shift Baru -->
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
    <h3 class="text-base font-semibold text-gray-800 mb-4">Buka Shift Baru</h3>
    <form method="POST" action="{{ route('shifts.open') }}" class="flex flex-wrap gap-4 items-end">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet <span class="text-red-500">*</span></label>
            <select name="outlet_id" required
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <option value="">— Pilih Outlet —</option>
                @foreach($outlets as $outlet)
                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modal Kas Awal (Rp)</label>
            <input type="number" name="cash_start" value="0" min="0" step="1000"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-36">
        </div>
        <button type="submit"
                class="px-5 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
            Buka Shift
        </button>
    </form>
</div>
@endif

<!-- Riwayat Shift -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-700">Riwayat Shift</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kasir</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Outlet</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Dibuka</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Ditutup</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Modal</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($shifts as $shift)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $shift->user->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $shift->outlet->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $shift->opened_at->format('d/m/y H:i') }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $shift->closed_at?->format('d/m/y H:i') ?? '-' }}</td>
                <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($shift->cash_start, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $shift->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $shift->status === 'open' ? 'Buka' : 'Tutup' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat shift.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(isset($shifts) && method_exists($shifts, 'hasPages') && $shifts->hasPages())
<div class="mt-4">{{ $shifts->links() }}</div>
@endif
@endsection
