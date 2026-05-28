<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Inventory;
use Modules\Core\Models\Outlet;
use Modules\Core\Services\StockService;

class InventoryController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $outlets = Outlet::where('store_id', $storeId)->where('is_active', true)->get();

        $query = Inventory::with(['variant.product', 'outlet', 'subRack.rack'])
            ->whereHas('outlet', fn ($q) => $q->where('store_id', $storeId));

        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('variant.product', fn ($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            );
        }
        if ($request->filled('low_stock') && $request->low_stock === '1') {
            $query->whereColumn('qty', '<=', 'min_qty')->where('min_qty', '>', 0);
        }

        $inventory = $query->paginate(20)->withQueryString();

        return view('core::inventory.index', compact('inventory', 'outlets'));
    }

    public function adjust(Inventory $inventory): View
    {
        $inventory->load(['variant.product', 'outlet']);
        return view('core::inventory.adjust', compact('inventory'));
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $request->validate([
            'new_qty' => 'required|numeric|min:0',
            'note'    => 'nullable|string|max:500',
        ]);

        try {
            $this->stockService->adjust(
                $inventory->id,
                (float) $request->new_qty,
                $request->note ?? 'Penyesuaian stok manual'
            );

            return redirect()->route('inventory.index')
                ->with('success', 'Stok berhasil disesuaikan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
