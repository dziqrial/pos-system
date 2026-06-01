@extends('layouts.app')

@section('title', 'Manajemen Outlet')
@section('page-title', 'Manajemen Outlet')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $outlets->total() }} outlet</p>
    <a href="{{ route('outlets.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Tambah Outlet
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($outlets as $outlet)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $outlet->name }}</h3>
                    @if($outlet->address)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $outlet->address }}</p>
                    @endif
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                         {{ $outlet->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $outlet->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        @if($outlet->phone)
        <p class="text-xs text-gray-500 mt-3">{{ $outlet->phone }}</p>
        @endif

        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('outlets.edit', $outlet) }}"
               class="flex-1 text-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition-colors">
                Edit
            </a>
            <form method="POST" action="{{ route('outlets.destroy', $outlet) }}"
                  onsubmit="return confirm('Hapus outlet ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium rounded-lg transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        <p class="text-sm">Belum ada outlet</p>
        <a href="{{ route('outlets.create') }}" class="text-indigo-600 text-sm mt-2 inline-block">Buat outlet pertama</a>
    </div>
    @endforelse
</div>

@if($outlets->hasPages())
<div class="mt-6">
    {{ $outlets->links() }}
</div>
@endif
@endsection
