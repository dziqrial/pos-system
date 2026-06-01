@extends('layouts.app')

@section('title', 'Manajemen Peran')
@section('page-title', 'Manajemen Peran')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $roles->count() }} peran ditemukan</p>
    <a href="{{ route('roles.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Tambah Peran
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($roles as $role)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="font-semibold text-gray-800 capitalize">{{ $role->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $role->users_count }} pengguna &bull; {{ $role->permissions_count }} izin
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('roles.edit', $role) }}"
                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </a>
                @if($role->users_count === 0)
                <form method="POST" action="{{ route('roles.destroy', $role) }}"
                      onsubmit="return confirm('Hapus peran ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-gray-400">
        <p class="text-sm">Belum ada peran</p>
    </div>
    @endforelse
</div>
@endsection
