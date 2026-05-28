@extends('layouts.app')

@section('title', 'Detail Jurnal')
@section('page-title', 'Detail Jurnal: ' . $journalEntry->code)

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('journal.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Jurnal
    </a>

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <!-- Header Info -->
        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Kode Jurnal</p>
                <p class="font-mono font-bold text-gray-800">{{ $journalEntry->code }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal</p>
                <p class="font-medium text-gray-800">{{ $journalEntry->date->format('d F Y') }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-gray-500">Keterangan</p>
                <p class="text-gray-800">{{ $journalEntry->description }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                @if($journalEntry->status === 'posted')
                <span class="inline-flex px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium">Posted</span>
                @else
                <span class="inline-flex px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">Draft</span>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-500">Dibuat oleh</p>
                <p class="text-gray-800 text-sm">{{ $journalEntry->user?->name ?? '-' }}</p>
            </div>
        </div>

        <!-- Journal Lines -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Baris Jurnal</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Akun</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Keterangan</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Debit</th>
                            <th class="text-right px-4 py-2 font-medium text-gray-600">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($journalEntry->lines as $line)
                        <tr>
                            <td class="px-4 py-2">
                                <span class="font-mono text-xs text-gray-500 mr-1">{{ $line->account?->code }}</span>
                                {{ $line->account?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ $line->description ?? '—' }}</td>
                            <td class="px-4 py-2 text-right font-medium">
                                @if($line->debit > 0)
                                Rp {{ number_format($line->debit, 0, ',', '.') }}
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right font-medium">
                                @if($line->credit > 0)
                                Rp {{ number_format($line->credit, 0, ',', '.') }}
                                @else
                                <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-sm font-semibold text-gray-700">Total</td>
                            <td class="px-4 py-2 text-right font-bold">Rp {{ number_format($journalEntry->lines->sum('debit'), 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-bold">Rp {{ number_format($journalEntry->lines->sum('credit'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($journalEntry->isBalanced())
            <p class="text-xs text-green-600 mt-2">Jurnal seimbang.</p>
            @else
            <p class="text-xs text-red-500 mt-2">Jurnal tidak seimbang!</p>
            @endif
        </div>

        @if($journalEntry->ref_type)
        <div class="pt-2 border-t border-gray-100">
            <p class="text-xs text-gray-500">Referensi: <span class="font-medium">{{ $journalEntry->ref_type }} #{{ $journalEntry->ref_id }}</span></p>
        </div>
        @endif
    </div>
</div>
@endsection
