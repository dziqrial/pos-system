@extends('layouts.app')

@section('title', 'Meja')
@section('page-title', 'Manajemen Meja')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Status meja per outlet</p>
    <a href="{{ route('tables.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
        + Tambah Meja
    </a>
</div>

@foreach($outlets as $outlet)
@php $outletTables = $tables->get($outlet->id, collect()); @endphp
<div class="mb-6">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $outlet->name }}</h3>
    @if($outletTables->count())
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
        @foreach($outletTables as $table)
        @php
            $statusColors = ['available' => 'bg-green-50 border-green-200 text-green-700', 'occupied' => 'bg-red-50 border-red-200 text-red-700', 'reserved' => 'bg-yellow-50 border-yellow-200 text-yellow-700'];
            $statusLabels = ['available' => 'Tersedia', 'occupied' => 'Terisi', 'reserved' => 'Reservasi'];
        @endphp
        <div class="border rounded-xl p-4 {{ $statusColors[$table->status] ?? 'bg-gray-50 border-gray-200' }}">
            <p class="text-lg font-bold">{{ $table->name }}</p>
            <p class="text-xs opacity-70">{{ $table->capacity }} kursi</p>
            <p class="text-xs font-medium mt-1">{{ $statusLabels[$table->status] ?? $table->status }}</p>
            <div class="mt-2 flex gap-1">
                <a href="{{ route('tables.edit', $table) }}" class="text-xs underline opacity-70 hover:opacity-100">Edit</a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-400">Belum ada meja.</p>
    @endif
</div>
@endforeach
@endsection
