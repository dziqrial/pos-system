<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\ProductVariant;
use Modules\Core\Services\ShiftService;
use Modules\Core\Services\StockService;
use Modules\Core\Services\TransactionService;

class CashierController extends Controller
{
    public function __construct(
        private readonly ShiftService       $shiftService,
        private readonly TransactionService $transactionService,
        private readonly StockService       $stockService,
    ) {}

    public function index(): View
    {
        $storeId     = auth()->user()->store_id;
        $outlets     = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        $outletId    = session('active_outlet_id') ?? $outlets->first()?->id;
        $activeShift = $outletId ? $this->shiftService->getActiveShift((int) $outletId) : null;
        $outlet      = $outletId ? $outlets->find($outletId) : null;

        return view('core::cashier.index', compact('outlets', 'outletId', 'activeShift', 'outlet'));
    }

    public function searchProduct(Request $request): JsonResponse
    {
        $storeId = auth()->user()->store_id;
        $q       = $request->q ?? '';
        $outletId = (int) ($request->outlet_id ?? session('active_outlet_id') ?? 0);

        $variants = ProductVariant::with('product')
            ->whereHas('product', fn ($query) => $query
                ->where('store_id', $storeId)
                ->where('is_active', true)
            )
            ->where(function ($query) use ($q) {
                $query->where('barcode', $q)
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhereHas('product', fn ($pq) =>
                        $pq->where('name', 'like', "%{$q}%")
                           ->orWhere('barcode', $q)
                    );
            })
            ->where('is_active', true)
            ->limit(10)
            ->get();

        return response()->json($variants->map(function ($variant) use ($outletId) {
            $stock = $outletId ? $this->stockService->getAvailableStock($variant->id, $outletId) : 0;
            return [
                'id'         => $variant->id,
                'name'       => $variant->product->name
                    . ($variant->product->has_variants ? ' - ' . $variant->name : ''),
                'barcode'    => $variant->barcode ?? $variant->product->barcode,
                'sku'        => $variant->sku,
                'price'      => $variant->price,
                'unit'       => $variant->unit,
                'unit_type'  => $variant->unit_type,
                'stock_type' => $variant->product->stock_type,
                'stock'      => $stock,
            ];
        }));
    }

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'outlet_id'               => 'required|exists:outlets,id',
            'items'                   => 'required|array|min:1',
            'items.*.variant_id'      => 'required|exists:product_variants,id',
            'items.*.qty'             => 'required|numeric|min:0.001',
            'items.*.discount'        => 'nullable|numeric|min:0',
            'payments'                => 'required|array|min:1',
            'payments.*.method'       => 'required|in:cash,qris,card,transfer,voucher,points',
            'payments.*.amount'       => 'required|numeric|min:0',
            'payments.*.reference_no' => 'nullable|string|max:255',
            'discount_amount'         => 'nullable|numeric|min:0',
            'customer_id'             => 'nullable|integer',
            'note'                    => 'nullable|string|max:500',
        ]);

        try {
            $transaction = $this->transactionService->createTransaction([
                'outlet_id'       => (int) $request->outlet_id,
                'items'           => $request->items,
                'payments'        => $request->payments,
                'discount_amount' => (float) ($request->discount_amount ?? 0),
                'customer_id'     => $request->customer_id,
                'note'            => $request->note,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => 'Transaksi berhasil.',
                'transaction' => $transaction,
                'receipt_url' => route('transactions.show', $transaction->id),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function setOutlet(Request $request): RedirectResponse
    {
        $request->validate(['outlet_id' => 'required|exists:outlets,id']);
        session(['active_outlet_id' => (int) $request->outlet_id]);
        return back()->with('success', 'Outlet aktif berhasil diganti.');
    }
}
