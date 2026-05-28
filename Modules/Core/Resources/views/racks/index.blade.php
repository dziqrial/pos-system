@extends('layouts.app')

@section('title', 'Rak')
@section('page-title', 'Manajemen Rak')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Lokasi penyimpanan stok per outlet</p>
    <a href="{{ route('racks.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Rak
    </a>
</div>

<div class="space-y-4">
    @forelse($racks as $rack)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-start justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $rack->name }}</h3>
                @if($rack->location_note)
                <p class="text-sm text-gray-500">📍 {{ $rack->location_note }}</p>
                @endif
                <p class="text-xs text-gray-400">Outlet: {{ $rack->outlet->name ?? '-' }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('racks.edit', $rack) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                <form method="POST" action="{{ route('racks.destroy', $rack) }}" onsubmit="return confirm('Hapus rak ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Hapus</button>
                </form>
            </div>
        </div>

        <!-- Sub-racks -->
        @if($rack->subRacks->count())
        <div class="flex flex-wrap gap-2 mb-3">
            @foreach($rack->subRacks as $sub)
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg px-3 py-1.5 text-sm">
                <span class="text-gray-700">{{ $sub->name }}</span>
                <form method="POST" action="{{ route('racks.sub-racks.destroy', [$rack, $sub]) }}"
                      onsubmit="return confirm('Hapus sub-rak?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 ml-1">×</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Add sub-rack form -->
        <div x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah Sub-Rak</button>
            <div x-show="open" x-collapse class="mt-2">
                <form method="POST" action="{{ route('racks.sub-racks.store', $rack) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Nama sub-rak (Baris 1, Shelf A, dll)" required
                           class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Tambah</button>
                    <button type="button" @click="open = false" class="px-3 py-1.5 bg-gray-200 text-gray-600 text-sm rounded hover:bg-gray-300">Batal</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
        Belum ada rak. Tambah rak untuk mengelola lokasi penyimpanan.
    </div>
    @endforelse
</div>

@if(isset($racks) && method_exists($racks, 'hasPages') && $racks->hasPages())
<div class="mt-4">{{ $racks->links() }}</div>
@endif
@endsection
