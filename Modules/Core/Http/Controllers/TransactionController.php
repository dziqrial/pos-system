<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Transaction;
use Modules\Core\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService) {}

    public function index(Request $request): View
    {
        $storeId      = auth()->user()->store_id;
        $transactions = Transaction::whereHas('outlet', fn ($q) => $q->where('store_id', $storeId))
            ->with(['outlet', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('search'), fn ($q) => $q->where('code', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('core::transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['items', 'payments', 'refunds', 'outlet', 'user', 'shift']);
        return view('core::transactions.show', compact('transaction'));
    }

    public function void(Request $request, Transaction $transaction): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->transactionService->voidTransaction($transaction, $request->reason ?? 'Manual void');
            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil di-void.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
