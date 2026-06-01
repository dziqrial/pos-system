<?php

namespace Modules\FnB\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\FnB\Models\KitchenItem;
use Modules\FnB\Models\KitchenOrder;

class KitchenController extends Controller
{
    public function index(): View
    {
        $storeId = auth()->user()->store_id;

        $orders = KitchenOrder::whereHas('transaction.outlet', fn ($q) => $q->where('store_id', $storeId))
            ->with(['transaction.outlet', 'table', 'items'])
            ->whereIn('status', ['pending', 'cooking', 'ready'])
            ->latest()
            ->get()
            ->groupBy('status');

        return view('fnb::kitchen.index', compact('orders'));
    }

    public function updateItemStatus(Request $request, KitchenOrder $kitchenOrder, KitchenItem $kitchenItem): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,cooking,done']);
        $kitchenItem->update(['status' => $request->status]);

        // If all items done → order is ready
        if ($kitchenOrder->items()->where('status', '!=', 'done')->doesntExist()) {
            $kitchenOrder->update(['status' => 'ready', 'cooked_at' => now()]);
        }

        return response()->json(['success' => true, 'order_status' => $kitchenOrder->fresh()->status]);
    }

    public function updateOrderStatus(Request $request, KitchenOrder $kitchenOrder): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,cooking,ready,served']);
        $data = ['status' => $request->status];

        if ($request->status === 'served') {
            $data['served_at'] = now();
        }

        $kitchenOrder->update($data);

        return response()->json(['success' => true]);
    }
}
