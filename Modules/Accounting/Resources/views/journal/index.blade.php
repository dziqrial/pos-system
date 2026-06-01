@extends('layouts.app')

@section('title', 'Jurnal')
@section('page-title', 'Buku Jurnal')

@section('content')
<div class="flex items-center justify-between mb-6">
    <form method="GET" action="{{ route('journal.index') }}" class="flex items-center gap-2">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <span class="text-gray-400 text-sm">s/d</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">Semua Status</option>
            <option value="draft"  {{ request('status') === 'draft'  ? 'selected' : '' }}>Draft</option>
            <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Filter</button>
    </form>
    <a href="{{ route('journal.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Jurnal Baru
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Kode</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Keterangan</th>
                <th class="text-right px-4 py-3 font-medium text-gray-600">Total Debit</th>
                <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($entries as $entry)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-gray-600">{{ $entry->code }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $entry->date->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-gray-800">{{ $entry->description }}</td>
                <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($entry->lines->sum('debit'), 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-center">
                    @if($entry->status === 'posted')
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">Posted</span>
                    @else
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs">Draft</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('journal.show', $entry) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada jurnal.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($entries->hasPages())
<div class="mt-4">{{ $entries->links() }}</div>
@endif
@endsection
