@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir POS')

@section('content')
@if(!$activeShift)
{{-- ===== BUKA SHIFT ===== --}}
<div class="max-w-lg mx-auto mt-8">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-amber-50 border-b border-amber-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Buka Shift Baru</h2>
                    <p class="text-sm text-gray-500">Belum ada shift aktif di outlet ini</p>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('shifts.open') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet</label>
                <select name="outlet_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($outlets as $o)
                    <option value="{{ $o->id }}" {{ $outletId == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kas Awal</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                    <input type="number" name="cash_start" value="0" min="0" step="1000" required
                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                <input type="text" name="note" maxlength="500"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition-colors">
                Buka Shift
            </button>
        </form>
    </div>
</div>

@else
{{-- ===== KASIR POS ===== --}}
<div
    x-data="kasirApp({{ $outletId ?? 'null' }}, '{{ route('kasir.search') }}', '{{ route('kasir.checkout') }}', '{{ csrf_token() }}')"
    class="flex gap-4 h-[calc(100vh-130px)] -my-4 lg:-my-6"
>

    {{-- ===== PANEL KIRI: Produk & Keranjang ===== --}}
    <div class="flex-1 flex flex-col gap-3 min-w-0 overflow-hidden">

        {{-- Search Bar --}}
        <div class="bg-white rounded-xl border border-gray-200 p-3 flex-shrink-0">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input.debounce.300ms="search()"
                        @keydown.enter.prevent="addFirstResult()"
                        placeholder="Cari produk / scan barcode..."
                        autofocus
                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
                <select x-model="outletId" @change="outletChanged()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($outlets as $o)
                    <option value="{{ $o->id }}">{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Search results dropdown --}}
            <div x-show="searchResults.length > 0 && searchQuery.length > 0"
                @click.outside="searchResults = []"
                class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                <template x-for="product in searchResults" :key="product.id">
                    <button
                        @click="addToCart(product); searchResults = []; searchQuery = ''"
                        class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 text-left border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-800" x-text="product.name"></p>
                            <p class="text-xs text-gray-400" x-text="'SKU: ' + product.sku + ' | Stok: ' + product.stock"></p>
                        </div>
                        <p class="text-sm font-semibold text-indigo-600" x-text="formatRupiah(product.price)"></p>
                    </button>
                </template>
            </div>
        </div>

        {{-- Keranjang --}}
        <div class="bg-white rounded-xl border border-gray-200 flex-1 overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <h3 class="text-sm font-semibold text-gray-700">Keranjang Belanja</h3>
                <button
                    x-show="cart.length > 0"
                    @click="clearCart()"
                    class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Kosongkan
                </button>
            </div>

            {{-- Empty state --}}
            <div x-show="cart.length === 0" class="flex-1 flex flex-col items-center justify-center text-gray-400 py-12">
                <svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm">Keranjang kosong</p>
                <p class="text-xs">Cari atau scan produk di atas</p>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="flex items-center gap-3 px-4 py-3">
                        {{-- Product info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></p>
                            <p class="text-xs text-gray-400" x-text="formatRupiah(item.price) + ' / ' + item.unit"></p>
                            {{-- Discount input --}}
                            <div class="flex items-center gap-2 mt-1">
                                <label class="text-xs text-gray-500">Diskon:</label>
                                <div class="relative">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                                    <input
                                        type="number"
                                        x-model.number="item.discount"
                                        @input="updateCart()"
                                        min="0"
                                        :max="item.price * item.qty"
                                        class="w-24 border border-gray-200 rounded pl-6 pr-1 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                                </div>
                            </div>
                        </div>

                        {{-- Qty control --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button @click="decrementQty(index)"
                                class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 text-lg leading-none">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <template x-if="item.unit_type === 'weight'">
                                <input type="number"
                                    x-model.number="item.qty"
                                    @input="updateCart()"
                                    min="0.001" step="0.001"
                                    class="w-16 text-center border border-gray-200 rounded py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                            </template>
                            <template x-if="item.unit_type !== 'weight'">
                                <input type="number"
                                    x-model.number="item.qty"
                                    @input="updateCart()"
                                    min="1" step="1"
                                    class="w-14 text-center border border-gray-200 rounded py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                            </template>
                            <button @click="incrementQty(index)"
                                class="w-7 h-7 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Subtotal & Remove --}}
                        <div class="text-right flex-shrink-0 w-24">
                            <p class="text-sm font-semibold text-gray-800" x-text="formatRupiah(item.subtotal)"></p>
                            <button @click="removeItem(index)" class="text-xs text-red-400 hover:text-red-600 mt-0.5">Hapus</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ===== PANEL KANAN: Ringkasan & Pembayaran ===== --}}
    <div class="w-80 flex flex-col gap-3 flex-shrink-0">

        {{-- Shift info --}}
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                <div>
                    <p class="text-xs font-medium text-gray-700">Shift Aktif</p>
                    <p class="text-xs text-gray-400">Dibuka: {{ $activeShift->opened_at->format('H:i') }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('shifts.close', $activeShift->id) }}" x-data="{ cashEnd: 0, note: '' }"
                @submit.prevent="
                    let c = prompt('Masukkan kas akhir (Rp):');
                    if (c !== null) {
                        cashEnd = parseFloat(c) || 0;
                        $el.submit();
                    }
                ">
                @csrf
                <input type="hidden" name="cash_end" :value="cashEnd">
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Tutup Shift</button>
            </form>
        </div>

        {{-- Order Summary --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex-shrink-0">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Pesanan</h3>

            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium" x-text="formatRupiah(subtotal)"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500">Diskon</span>
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-400">Rp</span>
                        <input type="number" x-model.number="globalDiscount" @input="updateCart()"
                            min="0" :max="subtotal"
                            class="w-24 text-right border border-gray-200 rounded px-2 py-0.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </div>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pajak</span>
                    <span x-text="formatRupiah(tax)"></span>
                </div>
                <div class="border-t border-gray-100 pt-2 mt-2 flex justify-between text-base font-bold">
                    <span>Total</span>
                    <span class="text-indigo-600" x-text="formatRupiah(total)"></span>
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex-1 overflow-y-auto">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Metode Pembayaran</h3>

            {{-- Payment methods --}}
            <div class="grid grid-cols-3 gap-2 mb-4">
                <template x-for="method in paymentMethods" :key="method.key">
                    <button
                        @click="selectPaymentMethod(method.key)"
                        :class="selectedMethod === method.key
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                        class="border rounded-lg py-2 px-1 text-xs font-medium text-center transition-colors">
                        <span x-text="method.label"></span>
                    </button>
                </template>
            </div>

            {{-- Payment amount --}}
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah Bayar</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input
                            type="number"
                            x-model.number="paidAmount"
                            @input="calculateChange()"
                            min="0"
                            class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Quick cash buttons --}}
                <div x-show="selectedMethod === 'cash'" class="flex gap-1.5 flex-wrap">
                    <template x-for="nominal in quickCashOptions" :key="nominal">
                        <button
                            @click="paidAmount = nominal; calculateChange()"
                            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-medium text-gray-600 transition-colors"
                            x-text="formatCompact(nominal)">
                        </button>
                    </template>
                    <button
                        @click="paidAmount = total; calculateChange()"
                        class="px-2 py-1 bg-indigo-100 hover:bg-indigo-200 rounded text-xs font-medium text-indigo-600 transition-colors">
                        Pas
                    </button>
                </div>

                {{-- Reference No (for non-cash) --}}
                <div x-show="selectedMethod !== 'cash'">
                    <label class="block text-xs font-medium text-gray-700 mb-1">No. Referensi</label>
                    <input type="text" x-model="referenceNo" placeholder="No. transaksi / kode"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Kembalian --}}
                <div x-show="selectedMethod === 'cash' && change >= 0"
                    class="bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                    <p class="text-xs text-green-600 font-medium">Kembalian</p>
                    <p class="text-lg font-bold text-green-700" x-text="formatRupiah(change)"></p>
                </div>

                {{-- Kurang bayar warning --}}
                <div x-show="selectedMethod === 'cash' && paidAmount > 0 && change < 0"
                    class="bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                    <p class="text-xs text-red-600 font-medium">Kurang</p>
                    <p class="text-base font-bold text-red-700" x-text="formatRupiah(Math.abs(change))"></p>
                </div>
            </div>
        </div>

        {{-- Checkout Button --}}
        <div class="flex-shrink-0">
            {{-- Error message --}}
            <div x-show="errorMessage" class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 mb-2">
                <p class="text-sm text-red-700" x-text="errorMessage"></p>
            </div>

            <button
                @click="checkout()"
                :disabled="!canCheckout || isLoading"
                :class="canCheckout && !isLoading
                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white'
                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                class="w-full font-bold py-4 rounded-xl transition-colors text-base flex items-center justify-center gap-2">
                <svg x-show="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="isLoading ? 'Memproses...' : 'Bayar ' + formatRupiah(total)"></span>
            </button>
        </div>
    </div>

    {{-- ===== SUCCESS MODAL ===== --}}
    <div x-show="showSuccessModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div x-show="showSuccessModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-1">Transaksi Berhasil!</h3>
            <p class="text-gray-500 text-sm mb-2" x-text="lastTransactionCode"></p>
            <div x-show="selectedMethod === 'cash' && change > 0"
                class="bg-green-50 rounded-xl p-3 mb-4">
                <p class="text-xs text-green-600">Kembalian</p>
                <p class="text-2xl font-bold text-green-700" x-text="formatRupiah(change)"></p>
            </div>
            <div class="flex gap-2">
                <a :href="receiptUrl" target="_blank"
                    class="flex-1 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2.5 rounded-lg text-sm transition-colors">
                    Lihat Struk
                </a>
                <button @click="resetPos()"
                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                    Transaksi Baru
                </button>
            </div>
        </div>
    </div>

</div>
@endif

@push('scripts')
<script>
function kasirApp(outletId, searchUrl, checkoutUrl, csrfToken) {
    return {
        outletId: outletId,
        searchUrl: searchUrl,
        checkoutUrl: checkoutUrl,
        csrfToken: csrfToken,

        searchQuery: '',
        searchResults: [],

        cart: [],
        globalDiscount: 0,
        taxRate: 0, // Will be set from server if needed

        selectedMethod: 'cash',
        paidAmount: 0,
        referenceNo: '',
        change: 0,

        paymentMethods: [
            { key: 'cash',     label: 'Tunai' },
            { key: 'qris',     label: 'QRIS' },
            { key: 'card',     label: 'Kartu' },
            { key: 'transfer', label: 'Transfer' },
            { key: 'voucher',  label: 'Voucher' },
            { key: 'points',   label: 'Poin' },
        ],

        isLoading: false,
        errorMessage: '',
        showSuccessModal: false,
        lastTransactionCode: '',
        receiptUrl: '',

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
        },
        get tax() {
            return (this.subtotal - this.globalDiscount) * (this.taxRate / 100);
        },
        get total() {
            const t = this.subtotal - this.globalDiscount + this.tax;
            return t < 0 ? 0 : t;
        },
        get quickCashOptions() {
            const t = this.total;
            const options = new Set();
            const bases = [1000, 2000, 5000, 10000, 20000, 50000, 100000, 200000];
            bases.forEach(b => {
                const val = Math.ceil(t / b) * b;
                if (val >= t) options.add(val);
            });
            return [...options].slice(0, 6);
        },
        get canCheckout() {
            if (this.cart.length === 0) return false;
            if (this.total <= 0) return false;
            if (this.selectedMethod === 'cash') {
                return this.paidAmount >= this.total;
            }
            return this.paidAmount > 0;
        },

        async search() {
            if (this.searchQuery.length < 1) {
                this.searchResults = [];
                return;
            }
            try {
                const res = await fetch(this.searchUrl + '?q=' + encodeURIComponent(this.searchQuery) + '&outlet_id=' + this.outletId);
                this.searchResults = await res.json();
            } catch (e) {
                this.searchResults = [];
            }
        },

        addFirstResult() {
            if (this.searchResults.length > 0) {
                this.addToCart(this.searchResults[0]);
                this.searchResults = [];
                this.searchQuery = '';
            }
        },

        addToCart(product) {
            if (product.stock <= 0 && product.stock_type !== 'serial') {
                this.errorMessage = 'Stok produk "' + product.name + '" habis!';
                setTimeout(() => this.errorMessage = '', 3000);
                return;
            }

            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                existing.qty += (product.unit_type === 'weight' ? 0.1 : 1);
                this.updateCart();
            } else {
                this.cart.push({
                    id:         product.id,
                    name:       product.name,
                    price:      product.price,
                    unit:       product.unit,
                    unit_type:  product.unit_type,
                    stock_type: product.stock_type,
                    stock:      product.stock,
                    qty:        product.unit_type === 'weight' ? 0.1 : 1,
                    discount:   0,
                    subtotal:   product.price,
                });
            }
            this.errorMessage = '';
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            this.updateCart();
        },

        clearCart() {
            if (confirm('Kosongkan keranjang?')) {
                this.cart = [];
                this.globalDiscount = 0;
                this.paidAmount = 0;
                this.change = 0;
            }
        },

        incrementQty(index) {
            const item = this.cart[index];
            item.qty += (item.unit_type === 'weight' ? 0.1 : 1);
            this.updateCart();
        },

        decrementQty(index) {
            const item = this.cart[index];
            const min = item.unit_type === 'weight' ? 0.001 : 1;
            if (item.qty <= min) {
                this.removeItem(index);
                return;
            }
            item.qty -= (item.unit_type === 'weight' ? 0.1 : 1);
            if (item.qty < min) item.qty = min;
            this.updateCart();
        },

        updateCart() {
            this.cart.forEach(item => {
                const raw = item.price * item.qty;
                item.subtotal = raw - (item.discount || 0);
                if (item.subtotal < 0) item.subtotal = 0;
            });
            this.calculateChange();
        },

        selectPaymentMethod(method) {
            this.selectedMethod = method;
            this.paidAmount = method === 'cash' ? 0 : this.total;
            this.referenceNo = '';
            this.calculateChange();
        },

        calculateChange() {
            this.change = this.paidAmount - this.total;
        },

        outletChanged() {
            // re-fetch stocks if needed
        },

        async checkout() {
            if (!this.canCheckout || this.isLoading) return;
            this.isLoading = true;
            this.errorMessage = '';

            const payload = {
                outlet_id:       this.outletId,
                items:           this.cart.map(i => ({
                    variant_id: i.id,
                    qty:        i.qty,
                    discount:   i.discount || 0,
                })),
                payments: [{
                    method:       this.selectedMethod,
                    amount:       this.paidAmount,
                    reference_no: this.referenceNo || null,
                }],
                discount_amount: this.globalDiscount,
            };

            try {
                const res  = await fetch(this.checkoutUrl, {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  this.csrfToken,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    this.lastTransactionCode = data.transaction?.code ?? '';
                    this.receiptUrl          = data.receipt_url ?? '#';
                    this.showSuccessModal    = true;
                } else {
                    this.errorMessage = data.message || 'Terjadi kesalahan.';
                }
            } catch (e) {
                this.errorMessage = 'Gagal terhubung ke server.';
            } finally {
                this.isLoading = false;
            }
        },

        resetPos() {
            this.cart           = [];
            this.globalDiscount = 0;
            this.paidAmount     = 0;
            this.change         = 0;
            this.referenceNo    = '';
            this.selectedMethod = 'cash';
            this.showSuccessModal = false;
            this.errorMessage   = '';
            this.$nextTick(() => {
                document.querySelector('input[placeholder]')?.focus();
            });
        },

        formatRupiah(n) {
            return 'Rp ' + (n || 0).toLocaleString('id-ID');
        },
        formatCompact(n) {
            if (n >= 1000000) return (n / 1000000) + 'jt';
            if (n >= 1000) return (n / 1000) + 'rb';
            return n;
        },
    };
}
</script>
@endpush
@endsection
