@extends('layouts.app')

@section('title', 'Manajemen Modul')
@section('page-title', 'Manajemen Modul')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Modul Sistem</h2>
        <p class="text-sm text-gray-500 mt-0.5">Aktifkan atau nonaktifkan fitur untuk toko Anda.</p>
    </div>

    <div class="divide-y divide-gray-100">
        @foreach($modules as $module)
        @php
            $storeModule = $module->storeModules->first();
            $isEnabled   = $module->is_core || ($storeModule?->is_enabled ?? false);
        @endphp
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $isEnabled ? 'bg-indigo-50' : 'bg-gray-100' }}">
                    <svg class="w-5 h-5 {{ $isEnabled ? 'text-indigo-600' : 'text-gray-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-800">{{ $module->name }}</p>
                        @if($module->is_core)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                            Inti
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Versi {{ $module->version }}
                        @if($storeModule?->enabled_at && $isEnabled)
                        &bull; Diaktifkan {{ $storeModule->enabled_at->diffForHumans() }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($module->is_core)
                <span class="text-xs text-gray-400">Selalu aktif</span>
                @else
                <form method="POST" action="{{ route('modules.toggle', $module) }}">
                    @csrf
                    <button type="submit"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               {{ $isEnabled ? 'bg-indigo-600' : 'bg-gray-200' }}"
                        title="{{ $isEnabled ? 'Nonaktifkan' : 'Aktifkan' }} {{ $module->name }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform
                                     {{ $isEnabled ? 'translate-x-6' : 'translate-x-1' }}">
                        </span>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
