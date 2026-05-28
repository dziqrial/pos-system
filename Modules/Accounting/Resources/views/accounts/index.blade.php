@extends('layouts.app')

@section('title', 'Chart of Accounts')
@section('page-title', 'Chart of Accounts')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Kelola akun-akun dalam sistem akuntansi</p>
    <a href="{{ route('accounts.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Tambah Akun
    </a>
</div>

@php
$typeLabels = [
    'asset'     => ['label' => 'Aset',       'color' => 'bg-blue-50 border-blue-200'],
    'liability' => ['label' => 'Kewajiban',   'color' => 'bg-red-50 border-red-200'],
    'equity'    => ['label' => 'Ekuitas',     'color' => 'bg-purple-50 border-purple-200'],
    'revenue'   => ['label' => 'Pendapatan',  'color' => 'bg-green-50 border-green-200'],
    'expense'   => ['label' => 'Beban',       'color' => 'bg-yellow-50 border-yellow-200'],
];
@endphp

@forelse($accounts as $type => $group)
@php $meta = $typeLabels[$type] ?? ['label' => $type, 'color' => 'bg-gray-50 border-gray-200']; @endphp
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $meta['label'] }}</h3>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-2 font-medium text-gray-600">Kode</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-600">Nama Akun</th>
                    <th class="text-right px-4 py-2 font-medium text-gray-600">Saldo</th>
                    <th class="text-center px-4 py-2 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($group as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-gray-600">{{ $account->code }}</td>
                    <td class="px-4 py-2 text-gray-800">{{ $account->name }}</td>
                    <td class="px-4 py-2 text-right font-medium">Rp {{ number_format($account->balance, 0, ',', '.') }}</td>
                    <td class="px-4 py-2 text-center">
                        @if($account->is_active)
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">Aktif</span>
                        @else
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-xs">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('accounts.edit', $account) }}" class="text-indigo-600 hover:text-indigo-800 text-xs">Edit</a>
                            <form method="POST" action="{{ route('accounts.destroy', $account) }}"
                                  onsubmit="return confirm('Hapus akun ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="text-center py-16 text-gray-400">
    <p class="text-lg">Belum ada akun.</p>
    <a href="{{ route('accounts.create') }}" class="text-indigo-600 text-sm mt-2 inline-block">+ Tambah akun pertama</a>
</div>
@endforelse
@endsection
