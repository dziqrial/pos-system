@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $product->name }}</h2>
                @if($product->barcode)
                <p class="text-sm font-mono text-gray-500">{{ $product->barcode }}</p>
                @endif
            </div>
            <a href="{{ route('products.edit', $product) }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Edit
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Kategori</p>
                <p class="font-medium text-gray-800">{{ $product->category?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Tipe Stok</p>
                <p class="font-medium text-gray-800">{{ ucfirst($product->stock_type) }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @if($product->description)
            <div class="col-span-2">
                <p class="text-gray-500">Deskripsi</p>
                <p class="text-gray-800">{{ $product->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Varian</h3>
        <div class="space-y-3">
            @foreach($product->variants as $variant)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-800">{{ $variant->name }}</p>
                    <p class="text-xs font-mono text-gray-400">{{ $variant->sku }}</p>
                </div>
                <div class="text-right text-sm">
                    <p class="font-semibold text-gray-800">Rp {{ number_format($variant->price, 0, ',', '.') }}</p>
                    <p class="text-gray-400 text-xs">{{ $variant->unit }} / {{ $variant->unit_type }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
