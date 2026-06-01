@extends('layouts.app')

@section('title', 'Tambah Toko')
@section('page-title', 'Tambah Toko')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Buat Toko Baru</h2>
            <p class="text-sm text-gray-500 mt-0.5">Isi informasi toko dan akun pemilik.</p>
        </div>

        <form method="POST" action="{{ route('stores.store') }}" class="px-6 py-6 space-y-6">
            @csrf

            {{-- Info Toko --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Informasi Toko</h3>
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nama Toko <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror"
                            placeholder="Nama toko Anda">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Tipe Toko <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white @error('type') border-red-400 @enderror">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="retail" {{ old('type') == 'retail' ? 'selected' : '' }}>Retail</option>
                                <option value="fnb" {{ old('type') == 'fnb' ? 'selected' : '' }}>Food & Beverage</option>
                                <option value="pharmacy" {{ old('type') == 'pharmacy' ? 'selected' : '' }}>Apotek</option>
                            </select>
                            @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Zona Waktu <span class="text-red-500">*</span>
                            </label>
                            <select id="timezone" name="timezone" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white @error('timezone') border-red-400 @enderror">
                                <option value="">-- Pilih Zona Waktu --</option>
                                <option value="Asia/Jakarta" {{ old('timezone') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                                <option value="Asia/Makassar" {{ old('timezone') == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                                <option value="Asia/Jayapura" {{ old('timezone') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                            </select>
                            @error('timezone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Pemilik --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Akun Pemilik</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nama Pemilik <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('owner_name') border-red-400 @enderror"
                                placeholder="Nama lengkap pemilik">
                            @error('owner_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Email Pemilik <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="owner_email" name="owner_email" value="{{ old('owner_email') }}" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('owner_email') border-red-400 @enderror"
                                placeholder="pemilik@toko.com">
                            @error('owner_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="owner_password" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="owner_password" name="owner_password" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('owner_password') border-red-400 @enderror"
                                placeholder="Minimal 8 karakter">
                            @error('owner_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="owner_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="owner_password_confirmation" name="owner_password_confirmation" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Ulangi password">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Buat Toko
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
