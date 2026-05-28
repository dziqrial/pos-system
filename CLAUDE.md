# CLAUDE.md — Modular POS System

Ini adalah panduan lengkap untuk Claude Code dalam mengerjakan project ini.
Baca file ini **seluruhnya** sebelum mulai ngoding apapun.

---

## 🧠 Konteks Project

**Nama:** Modular POS System  
**Stack:** Laravel 11 + MySQL + Tailwind CSS + Alpine.js + Vite  
**Arsitektur:** Modular (nwidart/laravel-modules) + Event-Driven  
**Tujuan:** Aplikasi Point of Sale yang fiturnya bisa di-aktifkan/nonaktifkan per toko (feature toggle)

---

## 🚀 Setup Awal (Jalankan sekali setelah clone)

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy env dan generate key
cp .env.example .env
php artisan key:generate

# 4. Isi .env dengan kredensial MySQL kamu, lalu:
php artisan migrate

# 5. Seed data awal (modules, permissions, default store)
php artisan db:seed

# 6. Jalankan dev server (2 terminal terpisah)
php artisan serve        # terminal 1
npm run dev              # terminal 2
```

---

## 📁 Struktur Project

```
pos-system/
├── app/                        # Laravel core app (minimal, logic ada di Modules)
│   ├── Http/
│   │   └── Middleware/
│   │       └── CheckModuleEnabled.php   # Middleware cek fitur aktif
│   └── Helpers/
│       └── Feature.php                  # Helper Feature::enabled('loyalty')
│
├── Modules/                    # Semua fitur ada di sini
│   ├── System/                 # Store, User, Role, Permission, AuditLog
│   ├── Core/                   # Outlet, Produk, Rak, Inventory, Transaksi (WAJIB)
│   ├── Loyalty/                # Customer, Poin, Voucher (OPSIONAL)
│   ├── PurchaseOrder/          # PO ke supplier (OPSIONAL)
│   ├── FnB/                    # Meja & Kitchen Display (OPSIONAL)
│   └── Accounting/             # Jurnal & Laporan Keuangan (OPSIONAL)
│
├── CLAUDE.md                   # File ini
├── composer.json
├── package.json
└── vite.config.js
```

### Struktur dalam setiap Module:
```
Modules/NamaModule/
├── Config/
│   └── config.php
├── Database/
│   ├── Migrations/
│   └── Seeders/
├── Events/             # Event yang di-fire modul ini
├── Listeners/          # Listener dari event modul lain
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Providers/
│   ├── NamaModuleServiceProvider.php
│   └── EventServiceProvider.php
├── Routes/
│   ├── web.php
│   └── api.php
├── Services/           # Business logic (bukan di Controller)
└── Resources/
    └── views/
```

---

## 🗄️ Skema Database

### Urutan tabel (penting untuk migration & foreign key):

**System Module** (migrate duluan):
1. `stores` — induk semua data
2. `modules` + `store_modules` — registry fitur
3. `roles` + `permissions` + `role_permissions` — RBAC
4. `users` — terikat ke store & role
5. `audit_logs` — log semua aksi

**Core Module** (migrate setelah System):
6. `outlets` — cabang/gerai toko
7. `shifts` — sesi kasir per outlet
8. `categories` — kategori produk (self-referencing untuk sub-kategori)
9. `products` + `product_variants` — master produk
10. `suppliers`
11. `racks` + `sub_racks` — lokasi penyimpanan stok
12. `inventory` — stok per varian per outlet per sub_rak
13. `stock_movements` — log semua perubahan stok
14. `transactions` + `transaction_items` + `payments` + `refunds`

**Optional Modules** (migrate terakhir, hanya jika module aktif):
- Loyalty: `customers`, `loyalty_points`, `vouchers`, `voucher_usages`
- PurchaseOrder: `purchase_orders`, `po_items`
- FnB: `tables`, `kitchen_orders`, `kitchen_items`
- Accounting: `accounts`, `journal_entries`, `journal_lines`

### Catatan penting skema:
- `transactions.customer_id` → tidak ada FK constraint ke `customers` karena customers ada di modul Loyalty yang opsional. Gunakan polymorphic atau cek manual.
- `inventory.sub_rack_id` → nullable. Produk bisa tidak punya lokasi rak.
- `transaction_items.name` & `.price` → SNAPSHOT. Selalu copy nilai saat transaksi, bukan FK ke produk. Ini agar laporan historis tetap akurat walau harga berubah.
- `transactions.meta` (JSON) → dipakai modul opsional untuk menyimpan data tambahan tanpa alter table. Contoh: `{"table_id": 5, "points_earned": 100}`
- `stock_movements.qty` → bisa positif (masuk) atau negatif (keluar)

---

## 🔌 Sistem Feature Toggle

### Cara cek apakah modul aktif:
```php
// Helper
use App\Helpers\Feature;

if (Feature::enabled('loyalty')) {
    // logika loyalty
}

// Di Blade
@moduleEnabled('loyalty')
    <a href="/loyalty">Loyalty</a>
@endmoduleEnabled

// Di Route (middleware)
Route::middleware(['module:loyalty'])->group(function () {
    Route::resource('customers', CustomerController::class);
});
```

### Cara menambah modul baru:
1. Buat folder `Modules/NamaModulBaru/` dengan struktur standar
2. Buat `ServiceProvider` dan daftarkan di `composer.json` autoload
3. Insert ke tabel `modules`: `key = 'nama_modul_baru', is_core = false`
4. Aktifkan per toko via `store_modules`
5. **Tidak perlu edit file Core/System apapun**

---

## 📡 Event-Driven Architecture

Semua komunikasi antar modul **wajib** lewat Events. Jangan import class dari modul lain secara langsung.

### Events yang sudah ada di Core:

| Event | Di-fire saat | Listener contoh |
|-------|-------------|-----------------|
| `Core\Events\TransactionCompleted` | Transaksi berhasil disimpan | Loyalty: tambah poin, Accounting: buat jurnal |
| `Core\Events\TransactionVoided` | Transaksi dibatalkan | Loyalty: rollback poin, Accounting: reverse jurnal |
| `Core\Events\StockUpdated` | Qty inventory berubah | Notif low stock, update dashboard real-time |
| `Core\Events\PurchaseOrderReceived` | PO diterima | Stok naik, Accounting: catat hutang dagang |
| `Core\Events\ShiftOpened` | Kasir buka shift | — |
| `Core\Events\ShiftClosed` | Kasir tutup shift | Rekap penjualan per shift |
| `System\Events\ModuleEnabled` | Modul diaktifkan di toko | Run seeder default modul |
| `System\Events\ModuleDisabled` | Modul dinonaktifkan | Cleanup data opsional |

### Contoh listener (di modul Loyalty):
```php
// Modules/Loyalty/Listeners/AwardPointsOnTransaction.php
class AwardPointsOnTransaction
{
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;
        
        // Cek apakah customer ada
        if (!$transaction->customer_id) return;
        
        // Hitung & tambah poin
        $points = (int) ($transaction->total / 10000); // 1 poin per 10rb
        // ... dst
    }
}
```

---

## 🏗️ Konvensi Koding

### Controller → Service → Model
```
Controller      → terima request, validasi, panggil Service, return response
Service         → business logic, fire events
Model           → query scope, relasi, mutator/accessor
```

**Jangan taruh business logic di Controller atau Model.**

### Naming convention:
- Models: `PascalCase` singular (`Product`, `StockMovement`)
- Controllers: `PascalCase` + `Controller` (`ProductController`)
- Services: `PascalCase` + `Service` (`TransactionService`, `StockService`)
- Events: Past tense (`TransactionCompleted`, `StockUpdated`)
- Listeners: Present tense verb (`AwardPointsOnTransaction`, `CreateJournalEntry`)
- Migrations: Timestamp prefix + descriptive name

### Validasi:
- Selalu gunakan Form Request (`php artisan make:request`)
- Jangan validasi di Controller langsung

### Response API (kalau ada endpoint API):
```php
// Sukses
return response()->json(['data' => $data, 'message' => 'Berhasil'], 200);

// Error
return response()->json(['message' => 'Tidak ditemukan'], 404);
```

---

## 🎨 Frontend Stack

- **Tailwind CSS** — utility-first styling
- **Alpine.js** — interaktivitas ringan (modal, dropdown, toggle)
- **Blade** — template engine Laravel
- **Vite** — bundler (jalankan `npm run dev` saat development)

Layout utama ada di `resources/views/layouts/app.blade.php`.  
Setiap modul punya views-nya sendiri di `Modules/NamaModule/Resources/views/`.

---

## ✅ Urutan Pengerjaan yang Disarankan

Kalau kamu baru mulai, kerjakan dalam urutan ini:

1. **System Module** — Store CRUD, Auth (login/register), Role & Permission
2. **Core Module** — Outlet, Kategori, Produk + Varian, Rak + Sub Rak
3. **Core Module** — Inventory & Stock Movement
4. **Core Module** — Kasir: Shift, Transaksi, Pembayaran, Struk
5. **Core Module** — Laporan dasar (penjualan harian, stok)
6. **Loyalty Module** — Customer, Poin, Voucher
7. **PurchaseOrder Module** — PO ke supplier, penerimaan barang
8. **FnB Module** — Meja, Kitchen Display
9. **Accounting Module** — COA, Jurnal otomatis
10. **System** — Dashboard admin: toggle modul per toko

---

## ⚠️ Hal yang JANGAN Dilakukan

- ❌ Jangan import Model dari modul lain langsung — gunakan Events
- ❌ Jangan taruh logic di Controller — pakai Service
- ❌ Jangan hardcode `store_id` — selalu ambil dari `auth()->user()->store_id`
- ❌ Jangan lupa snapshot `name` & `price` di `transaction_items`
- ❌ Jangan buat migration tanpa memperhatikan urutan foreign key
- ❌ Jangan langsung update `inventory.qty` — selalu lewat `StockService` agar `stock_movements` tercatat
- ❌ Jangan skip `audit_logs` untuk aksi-aksi penting (void transaksi, ubah harga, dll)

---

## 📞 Kontak Developer

Project ini dikerjakan oleh **Al**.  
Kalau ada yang ambigu atau keputusan arsitektur yang perlu didiskusikan, tanyakan dulu sebelum implementasi.

---

## 📦 Sistem Stok — 3 Tipe Produk

Setiap produk punya `stock_type` yang menentukan cara kerja stok dan UI kasir.

### 1. `normal` — Produk Barcode Biasa
Contoh: minuman kemasan, snack, obat.

**Alur kasir:**
```
Scan barcode → produk ditemukan → input qty (default 1)
→ stok berkurang sejumlah qty
→ stock_movements: type=out, qty=-N, ref_type=transaction
```

**Stok masuk:** PO diterima → qty naik, atau adjustment manual.

---

### 2. `serial` — Produk dengan Kode Unik per Unit
Contoh: voucher fisik, lisensi software, HP (tracking IMEI), dll.

**Stok disimpan di:** `product_serial_numbers` (bukan di `inventory.qty`)  
**Stok tersedia** = COUNT(*) WHERE status='available' AND outlet_id=X AND product_variant_id=Y

**Cara kode masuk:**
- **Manual** — diinput satu per satu saat terima barang (PO received)
- **Generate otomatis** — sistem generate dengan format yang dikonfigurasi (prefix + random string + checksum)
- **Import CSV** — bulk upload

**Alur kasir:**
```
Kasir scan barcode produk (atau cari manual) → input qty N
→ StockService::consumeSerials($variantId, $outletId, $qty) dipanggil:
   1. Ambil N record serial dengan status='available' secara RANDOM
      → ProductSerialNumber::where(...)->inRandomOrder()->limit($qty)->lockForUpdate()->get()
   2. Update status → 'sold', isi transaction_id, sold_at
   3. Catat stock_movements (qty=-N, ref_type=transaction)
   4. Simpan array serial_codes ke transaction_items.meta['serial_numbers']
      → ditampilkan di struk untuk customer
→ Jika stok available < qty yang diminta → TOLAK transaksi, tampilkan error
```

**Contoh data struk (meta):**
```json
{
  "serial_numbers": ["SN-ABC123", "SN-XYZ789", "SN-DEF456"]
}
```

**PENTING — race condition:**  
Gunakan `lockForUpdate()` saat SELECT serial yang akan di-sold, supaya tidak ada 2 kasir yang mengambil kode yang sama secara bersamaan (terutama di multi-outlet atau multi-kasir).

---

### 3. `bulk` — Produk Curah Tanpa Barcode
Contoh: terigu, beras, gula, yang dikemas sendiri dengan plastik biasa.

**Tidak bisa di-scan barcode** — kasir cari produk manual (search by name).

**`unit_type` di `product_variants`:**
- `pcs` — qty integer, misal "5 bungkus terigu 1kg"
- `weight` — qty desimal 3 angka, misal "2.500 kg" (kasir input dari timbangan)

**Alur kasir:**
```
Kasir search nama produk → pilih dari list
→ Input qty manual:
   - unit_type=pcs    → stepper integer (+1/-1) atau ketik angka
   - unit_type=weight → input desimal (keyboard numpad), label satuan tampil di sebelah
→ stok berkurang sejumlah qty
→ stock_movements: type=out, qty=-N
```

---

## 🔧 StockService — Satu Pintu Semua Perubahan Stok

**WAJIB** semua perubahan stok lewat `StockService`. Jangan update `inventory.qty` langsung dari Controller atau tempat lain.

```php
// Modules/Core/Services/StockService.php

class StockService
{
    // Kurangi stok (dipanggil saat transaksi)
    public function deduct(int $variantId, int $outletId, float $qty, array $meta = []): void

    // Tambah stok (dipanggil saat PO diterima atau adjustment)
    public function add(int $variantId, int $outletId, float $qty, array $meta = []): void

    // Khusus produk serial: ambil N kode random, tandai sold
    public function consumeSerials(int $variantId, int $outletId, int $qty, int $transactionId): array
    // return: ['serial_numbers' => ['SN-ABC', 'SN-XYZ', ...]]

    // Khusus produk serial: generate kode baru
    public function generateSerials(int $variantId, int $outletId, int $qty, string $prefix = ''): array

    // Cek stok tersedia (handle semua tipe)
    public function getAvailableStock(int $variantId, int $outletId): float|int
    // → normal/bulk: return inventory.qty
    // → serial: return COUNT serial WHERE status=available

    // Transfer stok antar outlet
    public function transfer(int $variantId, int $fromOutletId, int $toOutletId, float $qty): void
}
```

Setiap method di StockService **wajib** mencatat ke `stock_movements` dan **fire** event `StockUpdated`.

---

## 🖥️ UI Kasir — Behaviour per Tipe Produk

| Situasi | normal | serial | bulk |
|---------|--------|--------|------|
| Cara tambah ke keranjang | Scan barcode / search | Scan barcode produk / search | Search nama |
| Input qty | Stepper int atau scan ulang | Stepper int | Stepper int (pcs) atau input desimal (weight) |
| Validasi stok | inventory.qty ≥ qty | COUNT(serial available) ≥ qty | inventory.qty ≥ qty |
| Di struk | nama + qty | nama + qty + daftar serial codes | nama + qty + satuan |
| Saat void/refund | qty dikembalikan ke inventory | serial dikembalikan ke status 'available' | qty dikembalikan ke inventory |

