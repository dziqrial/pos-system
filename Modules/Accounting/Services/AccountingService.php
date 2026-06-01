<?php

namespace Modules\Accounting\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalLine;
use Modules\Core\Models\Transaction;

class AccountingService
{
    public function createJournalFromTransaction(Transaction $transaction): ?JournalEntry
    {
        $storeId = $transaction->outlet->store_id ?? null;

        if (!$storeId) {
            return null;
        }

        // Cek apakah modul accounting aktif untuk toko ini
        $isEnabled = DB::table('store_modules')
            ->join('modules', 'store_modules.module_id', '=', 'modules.id')
            ->where('modules.key', 'accounting')
            ->where('store_modules.store_id', $storeId)
            ->where('store_modules.is_enabled', true)
            ->exists();

        if (!$isEnabled) {
            return null;
        }

        $cashAccount    = Account::where('store_id', $storeId)->where('type', 'asset')->where('name', 'like', '%Kas%')->first();
        $revenueAccount = Account::where('store_id', $storeId)->where('type', 'revenue')->first();

        if (!$cashAccount || !$revenueAccount) {
            return null;
        }

        return DB::transaction(function () use ($transaction, $storeId, $cashAccount, $revenueAccount) {
            $count = JournalEntry::where('store_id', $storeId)->whereDate('created_at', now())->count();
            $code  = 'JRN-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'store_id'    => $storeId,
                'code'        => $code,
                'description' => "Penjualan transaksi {$transaction->code}",
                'date'        => $transaction->created_at->toDateString(),
                'ref_type'    => 'transaction',
                'ref_id'      => $transaction->id,
                'status'      => 'posted',
                'posted_at'   => now(),
                'user_id'     => $transaction->user_id,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $cashAccount->id,
                'description'      => 'Penerimaan kas',
                'debit'            => $transaction->total,
                'credit'           => 0,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $revenueAccount->id,
                'description'      => 'Pendapatan penjualan',
                'debit'            => 0,
                'credit'           => $transaction->total,
            ]);

            return $entry;
        });
    }

    public function createManualJournal(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $storeId = auth()->user()->store_id;

            $count = JournalEntry::where('store_id', $storeId)->whereDate('created_at', now())->count();
            $code  = 'JRN-' . now()->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'store_id'    => $storeId,
                'code'        => $code,
                'description' => $data['description'],
                'date'        => $data['date'],
                'ref_type'    => null,
                'ref_id'      => null,
                'status'      => 'draft',
                'posted_at'   => null,
                'user_id'     => auth()->id(),
            ]);

            foreach ($data['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'description'      => $line['description'] ?? null,
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                ]);
            }

            if (!$entry->isBalanced()) {
                throw new \RuntimeException('Jurnal tidak seimbang. Total debit harus sama dengan total kredit.');
            }

            if (isset($data['post']) && $data['post']) {
                $entry->update([
                    'status'    => 'posted',
                    'posted_at' => now(),
                ]);
            }

            return $entry;
        });
    }

    public function seedDefaultAccounts(int $storeId): void
    {
        $defaults = [
            ['code' => '1-1000', 'name' => 'Kas',                    'type' => 'asset'],
            ['code' => '1-2000', 'name' => 'Piutang Dagang',          'type' => 'asset'],
            ['code' => '1-3000', 'name' => 'Persediaan Barang',       'type' => 'asset'],
            ['code' => '2-1000', 'name' => 'Hutang Dagang',           'type' => 'liability'],
            ['code' => '3-1000', 'name' => 'Modal',                   'type' => 'equity'],
            ['code' => '4-1000', 'name' => 'Pendapatan Penjualan',    'type' => 'revenue'],
            ['code' => '5-1000', 'name' => 'Harga Pokok Penjualan',   'type' => 'expense'],
            ['code' => '5-2000', 'name' => 'Beban Operasional',       'type' => 'expense'],
        ];

        foreach ($defaults as $acc) {
            Account::firstOrCreate(
                ['store_id' => $storeId, 'code' => $acc['code']],
                array_merge($acc, ['store_id' => $storeId, 'is_active' => true])
            );
        }
    }
}
