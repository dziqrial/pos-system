<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Models\Inventory;
use Modules\Core\Models\Outlet;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $storeId  = auth()->user()->store_id;
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();
        $outletId = $request->outlet_id;

        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();

        $baseQuery = DB::table('transactions')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo]);

        if ($outletId) {
            $baseQuery->where('transactions.outlet_id', $outletId);
        }

        $summaryRaw = (clone $baseQuery)->selectRaw('
            COUNT(*) as transaction_count,
            COALESCE(SUM(total), 0) as total_revenue,
            COALESCE(SUM(discount_amount), 0) as total_discount,
            COALESCE(AVG(total), 0) as avg_transaction
        ')->first();

        $voidCount = DB::table('transactions')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'voided')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->count();

        $summary = [
            'transaction_count' => $summaryRaw->transaction_count ?? 0,
            'total_revenue'     => $summaryRaw->total_revenue ?? 0,
            'total_discount'    => $summaryRaw->total_discount ?? 0,
            'avg_transaction'   => $summaryRaw->avg_transaction ?? 0,
            'void_count'        => $voidCount,
        ];

        $dailySales = (clone $baseQuery)
            ->selectRaw('DATE(transactions.created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->when($outletId, fn ($q) => $q->where('transactions.outlet_id', $outletId))
            ->selectRaw('
                transaction_items.name,
                SUM(transaction_items.qty) as total_qty,
                SUM(transaction_items.subtotal) as total_revenue
            ')
            ->groupBy('transaction_items.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $paymentMethods = DB::table('payments')
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->when($outletId, fn ($q) => $q->where('transactions.outlet_id', $outletId))
            ->selectRaw('payments.method, SUM(payments.amount) as amount')
            ->groupBy('payments.method')
            ->orderByDesc('amount')
            ->get();

        $lowStockItems = Inventory::with(['productVariant.product', 'outlet'])
            ->whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->when($outletId, fn ($q) => $q->where('outlet_id', $outletId))
            ->where('min_qty', '>', 0)
            ->whereColumn('qty', '<=', 'min_qty')
            ->limit(10)
            ->get();

        return view('core::reports.index', compact(
            'summary', 'dailySales', 'topProducts', 'paymentMethods',
            'lowStockItems', 'outlets', 'dateFrom', 'dateTo'
        ));
    }

    public function exportPdf(Request $request): Response
    {
        // Reuse the same data-fetching logic as index()
        $storeId  = auth()->user()->store_id;
        $dateFrom = $request->date_from ?? now()->startOfMonth()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();
        $outletId = $request->outlet_id;

        $baseQuery = DB::table('transactions')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo]);

        if ($outletId) {
            $baseQuery->where('transactions.outlet_id', $outletId);
        }

        $summaryRaw = (clone $baseQuery)->selectRaw('
            COUNT(*) as transaction_count,
            COALESCE(SUM(total), 0) as total_revenue,
            COALESCE(SUM(discount_amount), 0) as total_discount,
            COALESCE(AVG(total), 0) as avg_transaction
        ')->first();

        $voidCount = DB::table('transactions')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'voided')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->count();

        $summary = [
            'transaction_count' => $summaryRaw->transaction_count ?? 0,
            'total_revenue'     => $summaryRaw->total_revenue ?? 0,
            'total_discount'    => $summaryRaw->total_discount ?? 0,
            'avg_transaction'   => $summaryRaw->avg_transaction ?? 0,
            'void_count'        => $voidCount,
        ];

        $dailySales = (clone $baseQuery)
            ->selectRaw('DATE(transactions.created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->when($outletId, fn ($q) => $q->where('transactions.outlet_id', $outletId))
            ->selectRaw('transaction_items.name, SUM(transaction_items.qty) as total_qty, SUM(transaction_items.subtotal) as total_revenue')
            ->groupBy('transaction_items.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $paymentMethods = DB::table('payments')
            ->join('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
            ->where('outlets.store_id', $storeId)
            ->where('transactions.status', 'completed')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dateFrom, $dateTo])
            ->when($outletId, fn ($q) => $q->where('transactions.outlet_id', $outletId))
            ->selectRaw('payments.method, SUM(payments.amount) as amount')
            ->groupBy('payments.method')
            ->orderByDesc('amount')
            ->get();

        $pdf = Pdf::loadView('core::reports.pdf', compact(
            'summary', 'dailySales', 'topProducts', 'paymentMethods', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'portrait');

        $filename = 'laporan-' . $dateFrom . '-sd-' . $dateTo . '.pdf';

        return $pdf->download($filename);
    }
}
