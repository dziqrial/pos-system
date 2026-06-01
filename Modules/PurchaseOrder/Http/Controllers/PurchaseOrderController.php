<?php

namespace Modules\PurchaseOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Outlet;
use Modules\Core\Models\ProductVariant;
use Modules\Core\Models\Supplier;
use Modules\Core\Services\StockService;
use Modules\Core\Events\PurchaseOrderReceived;
use Modules\PurchaseOrder\Models\PoItem;
use Modules\PurchaseOrder\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(Request $request): View
    {
        $storeId = auth()->user()->store_id;
        $pos     = PurchaseOrder::whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->with(['outlet', 'supplier', 'user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('purchase_order::purchase-orders.index', compact('pos'));
    }

    public function create(): View
    {
        $storeId   = auth()->user()->store_id;
        $outlets   = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        $suppliers = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $variants  = ProductVariant::with('product')
            ->whereHas('product', fn ($q) => $q->where('store_id', $storeId)->where('is_active', true))
            ->get();

        return view('purchase_order::purchase-orders.create', compact('outlets', 'suppliers', 'variants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'outlet_id'   => 'required|exists:outlets,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_at' => 'nullable|date',
            'note'        => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.qty_ordered'        => 'required|numeric|min:0.001',
            'items.*.price'              => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'outlet_id'   => $request->outlet_id,
            'supplier_id' => $request->supplier_id,
            'user_id'     => auth()->id(),
            'po_number'   => 'PO-' . now()->format('Ymd') . '-' . str_pad(PurchaseOrder::count() + 1, 4, '0', STR_PAD_LEFT),
            'status'      => 'draft',
            'expected_at' => $request->expected_at,
            'note'        => $request->note,
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $subtotal = $item['qty_ordered'] * $item['price'];
            $total   += $subtotal;

            PoItem::create([
                'purchase_order_id'  => $po->id,
                'product_variant_id' => $item['product_variant_id'],
                'sub_rack_id'        => $item['sub_rack_id'] ?? null,
                'qty_ordered'        => $item['qty_ordered'],
                'qty_received'       => 0,
                'price'              => $item['price'],
                'subtotal'           => $subtotal,
            ]);
        }

        $po->update(['total' => $total]);

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['outlet', 'supplier', 'user', 'items.productVariant.product']);
        return view('purchase_order::purchase-orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Hanya PO berstatus draft yang bisa diedit.');

        $storeId   = auth()->user()->store_id;
        $outlets   = Outlet::where('store_id', $storeId)->where('is_active', true)->get();
        $suppliers = Supplier::where('store_id', $storeId)->where('is_active', true)->get();
        $variants  = ProductVariant::with('product')
            ->whereHas('product', fn ($q) => $q->where('store_id', $storeId)->where('is_active', true))
            ->get();

        $purchaseOrder->load('items.productVariant');

        return view('purchase_order::purchase-orders.edit', compact('purchaseOrder', 'outlets', 'suppliers', 'variants'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_at' => 'nullable|date',
            'note'        => 'nullable|string',
        ]);

        $purchaseOrder->update([
            'supplier_id' => $request->supplier_id,
            'expected_at' => $request->expected_at,
            'note'        => $request->note,
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'PO berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403, 'Hanya draft yang bisa dihapus.');
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'PO berhasil dihapus.');
    }

    public function submit(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless($purchaseOrder->status === 'draft', 403);
        $purchaseOrder->update(['status' => 'sent']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'PO berhasil dikirim ke supplier.');
    }

    public function receiveForm(PurchaseOrder $purchaseOrder): View
    {
        abort_unless(in_array($purchaseOrder->status, ['sent', 'partial']), 403);
        $purchaseOrder->load(['items.productVariant.product', 'outlet']);

        return view('purchase_order::purchase-orders.receive', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(in_array($purchaseOrder->status, ['sent', 'partial']), 403);

        $request->validate([
            'items'                       => 'required|array',
            'items.*.qty_received'        => 'required|numeric|min:0',
        ]);

        $allReceived = true;

        foreach ($purchaseOrder->items as $item) {
            $qtyReceived = (float) ($request->items[$item->id]['qty_received'] ?? 0);
            if ($qtyReceived <= 0) {
                $allReceived = false;
                continue;
            }

            $item->increment('qty_received', $qtyReceived);

            $this->stockService->add(
                $item->product_variant_id,
                $purchaseOrder->outlet_id,
                $qtyReceived,
                ['ref_type' => 'purchase_order', 'ref_id' => $purchaseOrder->id, 'note' => "Terima PO #{$purchaseOrder->po_number}"]
            );

            if ($item->qty_received < $item->qty_ordered) {
                $allReceived = false;
            }
        }

        $purchaseOrder->update(['status' => $allReceived ? 'received' : 'partial']);

        event(new PurchaseOrderReceived($purchaseOrder));

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Penerimaan barang berhasil dicatat.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        abort_unless(in_array($purchaseOrder->status, ['draft', 'sent']), 403);
        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'PO berhasil dibatalkan.');
    }
}
