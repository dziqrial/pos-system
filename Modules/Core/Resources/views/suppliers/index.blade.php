@extends('layouts.app')

@section('title', 'Supplier')
@section('page-title', 'Daftar Supplier')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $suppliers->total() }} supplier</p>
    <a href="{{ route('suppliers.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Supplier
    </a>
</div>

<form method="GET" class="mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari supplier..."
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 w-52">
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Cari</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Nama</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Kontak</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Telepon</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($suppliers as $supplier)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $supplier->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $supplier->contact_name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $supplier->phone ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                              onsubmit="return confirm('Hapus supplier ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada supplier.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($suppliers->hasPages())
<div class="mt-4">{{ $suppliers->links() }}</div>
@endif
@endsection
