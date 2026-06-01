@extends('layouts.app')

@section('title', 'Jurnal Baru')
@section('page-title', 'Buat Jurnal Manual')

@section('content')
<div class="max-w-3xl">
    <a href="{{ route('journal.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('journal.store') }}" class="space-y-5" x-data="journalForm()">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description') }}" required placeholder="Keterangan transaksi"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                </div>
            </div>

            <!-- Lines -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Baris Jurnal</label>
                    <button type="button" @click="addLine()"
                            class="text-xs text-indigo-600 hover:text-indigo-800">+ Tambah Baris</button>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-gray-600 w-2/5">Akun</th>
                                <th class="text-left px-3 py-2 font-medium text-gray-600">Keterangan</th>
                                <th class="text-right px-3 py-2 font-medium text-gray-600 w-28">Debit</th>
                                <th class="text-right px-3 py-2 font-medium text-gray-600 w-28">Kredit</th>
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="index">
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="px-3 py-2">
                                        <select :name="`lines[${index}][account_id]`" required
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                            <option value="">— Pilih Akun —</option>
                                            @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="`lines[${index}][description]`" placeholder="Keterangan"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" :name="`lines[${index}][debit]`" x-model.number="line.debit"
                                               min="0" step="1" placeholder="0"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" :name="`lines[${index}][credit]`" x-model.number="line.credit"
                                               min="0" step="1" placeholder="0"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeLine(index)" x-show="lines.length > 2"
                                                class="text-red-400 hover:text-red-600">×</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-3 py-2 text-xs font-medium text-gray-500">
                                    <span :class="isBalanced() ? 'text-green-600' : 'text-red-500'">
                                        <span x-show="isBalanced()">Seimbang</span>
                                        <span x-show="!isBalanced()">Tidak seimbang</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-medium text-sm" x-text="'Rp ' + formatNum(totalDebit())"></td>
                                <td class="px-3 py-2 text-right font-medium text-sm" x-text="'Rp ' + formatNum(totalCredit())"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="post" id="post" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-300">
                <label for="post" class="text-sm text-gray-700">Langsung posting (status: Posted)</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Simpan</button>
                <a href="{{ route('journal.index') }}" class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function journalForm() {
    return {
        lines: [
            { debit: 0, credit: 0 },
            { debit: 0, credit: 0 },
        ],
        addLine() {
            this.lines.push({ debit: 0, credit: 0 });
        },
        removeLine(index) {
            if (this.lines.length > 2) {
                this.lines.splice(index, 1);
            }
        },
        totalDebit() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.debit) || 0), 0);
        },
        totalCredit() {
            return this.lines.reduce((s, l) => s + (parseFloat(l.credit) || 0), 0);
        },
        isBalanced() {
            return Math.abs(this.totalDebit() - this.totalCredit()) < 0.001 && this.totalDebit() > 0;
        },
        formatNum(n) {
            return new Intl.NumberFormat('id-ID').format(n);
        }
    };
}
</script>
@endsection
