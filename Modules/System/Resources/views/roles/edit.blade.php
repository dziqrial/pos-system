@extends('layouts.app')

@section('title', 'Edit Peran')
@section('page-title', 'Edit Peran')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">Edit Peran: {{ ucfirst($role->name) }}</h2>
        </div>
        <form method="POST" action="{{ route('roles.update', $role) }}" class="px-6 py-6 space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Peran</label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                           @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            @if($permissions->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Izin Akses</label>
                <div class="space-y-4">
                    @foreach($permissions as $moduleKey => $perms)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $moduleKey }}</p>
                        <div class="space-y-2">
                            @foreach($perms as $perm)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                    {{ in_array($perm->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">{{ $perm->name }}</span>
                                @if($perm->description)
                                <span class="text-xs text-gray-400">— {{ $perm->description }}</span>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('roles.index') }}"
                   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
