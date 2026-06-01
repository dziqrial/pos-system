@extends('layouts.app')

@section('title', 'Edit Toko')
@section('page-title', 'Edit Toko')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Edit Toko: {{ $store->name }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">Perbarui informasi toko.</p>
        </div>

        <form method="POST" action="{{ route('stores.update', $store) }}" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Nama Toko --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Toko <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $store->name) }}" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                    placeholder="Nama toko">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Slug (readonly) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Slug</label>
                <input type="text" value="{{ $store->slug }}" disabled
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Slug tidak dapat diubah setelah toko dibuat.</p>
            </div>

            {{-- Tipe & Zona Waktu --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tipe Toko <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white @error('type') border-red-400 @enderror">
                        <option value="retail" {{ old('type', $store->type) == 'retail' ? 'selected' : '' }}>Retail</option>
                        <option value="fnb" {{ old('type', $store->type) == 'fnb' ? 'selected' : '' }}>Food & Beverage</option>
                        <option value="pharmacy" {{ old('type', $store->type) == 'pharmacy' ? 'selected' : '' }}>Apotek</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Zona Waktu <span class="text-red-500">*</span>
                    </label>
                    <select id="timezone" name="timezone" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white @error('timezone') border-red-400 @enderror">
                        <option value="Asia/Jakarta" {{ old('timezone', $store->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                        <option value="Asia/Makassar" {{ old('timezone', $store->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                        <option value="Asia/Jayapura" {{ old('timezone', $store->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                    </select>
                    @error('timezone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Status Aktif --}}
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                    {{ old('is_active', $store->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Toko aktif</label>
            </div>

            {{-- Tombol --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('stores.index') }}"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
