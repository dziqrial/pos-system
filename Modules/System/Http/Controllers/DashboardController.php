<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;
        $today   = now()->toDateString();

        $stats = [
            'revenue_today'      => 0,
            'transactions_today' => 0,
            'low_stock_count'    => 0,
            'total_products'     => 0,
        ];

        try {
            $stats['revenue_today'] = DB::table('transactions')
                ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
                ->where('outlets.store_id', $storeId)
                ->where('transactions.status', 'completed')
                ->whereDate('transactions.created_at', $today)
                ->sum('transactions.total');

            $stats['transactions_today'] = DB::table('transactions')
                ->join('outlets', 'transactions.outlet_id', '=', 'outlets.id')
                ->where('outlets.store_id', $storeId)
                ->where('transactions.status', 'completed')
                ->whereDate('transactions.created_at', $today)
                ->count();

            $stats['total_products'] = DB::table('products')
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->count();

            $stats['low_stock_count'] = DB::table('inventory')
                ->join('outlets', 'inventory.outlet_id', '=', 'outlets.id')
                ->where('outlets.store_id', $storeId)
                ->whereColumn('inventory.qty', '<=', 'inventory.min_qty')
                ->where('inventory.min_qty', '>', 0)
                ->count();
        } catch (\Exception) {
            // Tables may not exist yet — silently ignore
        }

        return view('system::dashboard.index', compact('stats'));
    }
}
