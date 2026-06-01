<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\AccountingService;

class JournalController extends Controller
{
    public function __construct(private AccountingService $accountingService) {}

    public function index(Request $request)
    {
        $storeId = auth()->user()->store_id;

        $query = JournalEntry::where('store_id', $storeId)
            ->with(['lines.account', 'user'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $entries = $query->paginate(20)->withQueryString();

        return view('accounting::journal.index', compact('entries'));
    }

    public function create()
    {
        $storeId  = auth()->user()->store_id;
        $accounts = Account::where('store_id', $storeId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('accounting::journal.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description'          => 'required|string|max:255',
            'date'                 => 'required|date',
            'lines'                => 'required|array|min:2',
            'lines.*.account_id'   => 'required|exists:accounts,id',
            'lines.*.description'  => 'nullable|string|max:255',
            'lines.*.debit'        => 'nullable|numeric|min:0',
            'lines.*.credit'       => 'nullable|numeric|min:0',
        ]);

        try {
            $this->accountingService->createManualJournal([
                'description' => $request->description,
                'date'        => $request->date,
                'lines'       => $request->lines,
                'post'        => $request->boolean('post'),
            ]);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('journal.index')->with('success', 'Jurnal berhasil disimpan.');
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'user']);

        return view('accounting::journal.show', compact('journalEntry'));
    }
}
