@extends('layouts.app')

@section('title', 'Tambah Akun')
@section('page-title', 'Tambah Akun')

@section('content')
<div class="max-w-lg">
    <a href="{{ route('accounts.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Akun <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="1-1000"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Kas, Piutang Dagang, dll"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Akun <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Pilih Tipe —</option>
                    <option value="asset"     {{ old('type') === 'asset'     ? 'selected' : '' }}>Aset</option>
                    <option value="liability" {{ old('type') === 'liability' ? 'selected' : '' }}>Kewajiban</option>
                    <option value="equity"    {{ old('type') === 'equity'    ? 'selected' : '' }}>Ekuitas</option>
                    <option value="revenue"   {{ old('type') === 'revenue'   ? 'selected' : '' }}>Pendapatan</option>
                    <option value="expense"   {{ old('type') === 'expense'   ? 'selected' : '' }}>Beban</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Induk Akun</label>
                <select name="parent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <option value="">— Tidak ada induk —</option>
                    @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->code }} - {{ $parent->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Simpan</button>
                <a href="{{ route('accounts.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
