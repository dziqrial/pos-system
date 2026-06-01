<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
    .header { background: #4f46e5; color: white; padding: 20px 24px; margin-bottom: 20px; }
    .header h1 { font-size: 18px; font-weight: bold; }
    .header p { font-size: 11px; opacity: 0.85; margin-top: 4px; }
    .content { padding: 0 24px; }
    .cards { display: table; width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 8px; }
    .card { display: table-cell; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; width: 25%; }
    .card-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
    .card-value { font-size: 16px; font-weight: bold; color: #111827; }
    .section { margin-bottom: 20px; }
    .section h2 { font-size: 12px; font-weight: bold; color: #374151; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th { background: #f3f4f6; text-align: left; padding: 6px 8px; font-weight: bold; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
    td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; color: #374151; }
    .text-right { text-align: right; }
    .footer { margin-top: 24px; padding: 12px 24px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; display: flex; justify-content: space-between; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
    .badge-green { background: #d1fae5; color: #065f46; }
    .badge-red { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>

<div class="header">
    <h1>Laporan Penjualan</h1>
    <p>Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d F Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d F Y') }}</p>
    <p>Dicetak: {{ now()->format('d F Y, H:i') }}</p>
</div>

<div class="content">

    <!-- Summary cards -->
    <table class="cards" style="margin-bottom:16px;">
        <tr>
            <td class="card">
                <div class="card-label">Total Penjualan</div>
                <div class="card-value">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
                <div style="font-size:9px;color:#6b7280;margin-top:2px;">{{ $summary['transaction_count'] }} transaksi</div>
            </td>
            <td class="card">
                <div class="card-label">Rata-rata Transaksi</div>
                <div class="card-value">Rp {{ number_format($summary['avg_transaction'], 0, ',', '.') }}</div>
            </td>
            <td class="card">
                <div class="card-label">Total Diskon</div>
                <div class="card-value" style="color:#dc2626;">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</div>
            </td>
            <td class="card">
                <div class="card-label">Transaksi Void</div>
                <div class="card-value" style="color:#6b7280;">{{ $summary['void_count'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Penjualan Harian -->
    @if(count($dailySales))
    <div class="section">
        <h2>Penjualan Harian</h2>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="text-right">Jumlah Transaksi</th>
                    <th class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailySales as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                    <td class="text-right">{{ $day->count }}</td>
                    <td class="text-right">Rp {{ number_format($day->revenue, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Produk Terlaris -->
    @if(count($topProducts))
    <div class="section">
        <h2>Produk Terlaris (Top 10)</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th class="text-right">Qty Terjual</th>
                    <th class="text-right">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">{{ number_format($item->total_qty, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Metode Pembayaran -->
    @if(count($paymentMethods))
    <div class="section">
        <h2>Metode Pembayaran</h2>
        <table>
            <thead>
                <tr>
                    <th>Metode</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentMethods as $m)
                <tr>
                    <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $m->method) }}</td>
                    <td class="text-right">Rp {{ number_format($m->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>

<div class="footer">
    <span>POS System — Laporan otomatis</span>
    <span>{{ now()->format('d/m/Y H:i') }}</span>
</div>

</body>
</html>
