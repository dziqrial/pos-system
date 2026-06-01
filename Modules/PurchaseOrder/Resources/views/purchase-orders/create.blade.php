@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('page-title', 'Buat Purchase Order')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>

    <form method="POST" action="{{ route('purchase-orders.store') }}" x-data="poForm()">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4 space-y-4">
            <h3 class="text-base font-semibold text-gray-800 pb-2 border-b border-gray-100">Informasi PO</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Outlet <span class="text-red-500">*</span></label>
                    <select name="outlet_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Pilih Outlet —</option>
                        @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}" {{ old('outlet_id') == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">— Pilih Supplier —</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Estimasi Terima</label>
                    <input type="date" name="expected_at" value="{{ old('expected_at') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <input type="text" name="note" value="{{ old('note') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100 mb-4">
                <h3 class="text-base font-semibold text-gray-800">Item Pesanan</h3>
                <button type="button" @click="addItem()"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah Item</button>
            </div>

            <div class="space-y-2" id="items-container">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Produk / Varian</label>
                            <select :name="`items[${idx}][product_variant_id]`" x-model="item.variant_id" required
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                <option value="">— Pilih —</option>
                                @foreach($variants as $v)
                                <option value="{{ $v->id }}">{{ $v->product->name }} — {{ $v->name }} ({{ $v->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-xs text-gray-500 mb-1">Qty</label>
                            <input type="number" :name="`items[${idx}][qty_ordered]`" x-model="item.qty" required min="0.001" step="0.001"
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs text-gray-500 mb-1">Harga Beli (Rp)</label>
                            <input type="number" :name="`items[${idx}][price]`" x-model="item.price" required min="0" step="100"
                                   class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                        </div>
                        <div class="pb-1">
                            <button type="button" @click="removeItem(idx)"
                                    class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 text-right border-t border-gray-100 pt-3">
                <p class="text-sm text-gray-500">Estimasi Total: <strong class="text-gray-800">Rp <span x-text="formatNumber(total)"></span></strong></p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">
                Buat PO (Draft)
            </button>
            <a href="{{ route('purchase-orders.index') }}"
               class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function poForm() {
    return {
        items: [{ variant_id: '', qty: 1, price: 0 }],
        get total() {
            return this.items.reduce((sum, i) => sum + (parseFloat(i.qty || 0) * parseFloat(i.price || 0)), 0);
        },
        addItem() { this.items.push({ variant_id: '', qty: 1, price: 0 }); },
        removeItem(idx) { if (this.items.length > 1) this.items.splice(idx, 1); },
        formatNumber(n) { return new Intl.NumberFormat('id-ID').format(n); }
    };
}
</script>
@endsection
