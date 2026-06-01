@extends('layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kategori Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-500">{{ $categories->total() }} kategori ditemukan</p>
    </div>
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Kategori
    </a>
</div>

<!-- Search -->
<form method="GET" class="mb-4">
    <div class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Cari kategori..."
               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200">Cari</button>
        @if(request('search'))
        <a href="{{ route('categories.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Nama</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Induk</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Urutan</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($categories as $category)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">
                    @if($category->icon)
                    <span class="mr-2">{{ $category->icon }}</span>
                    @endif
                    {{ $category->name }}
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $category->parent?->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $category->sort_order }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('categories.edit', $category) }}"
                           class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}"
                              onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada kategori.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($categories->hasPages())
<div class="mt-4">{{ $categories->links() }}</div>
@endif
@endsection
