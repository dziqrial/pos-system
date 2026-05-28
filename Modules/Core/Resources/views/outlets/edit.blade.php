@extends('layouts.app')

@section('title', 'Edit Outlet')
@section('page-title', 'Edit Outlet')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Edit: {{ $outlet->name }}</h2>
        </div>
        <form method="POST" action="{{ route('outlets.update', $outlet) }}" class="px-6 py-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Outlet</label>
                <input type="text" name="name" value="{{ old('name', $outlet->name) }}" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                           @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode Outlet</label>
                <input type="text" name="code" value="{{ old('code', $outlet->code ?? '') }}" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                           @error('code') border-red-400 @enderror">
                @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                <textarea name="address" rows="2"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $outlet->address) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $outlet->phone) }}"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $outlet->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="text-sm text-gray-700">Outlet aktif</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('outlets.index') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
